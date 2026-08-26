<?php

declare(strict_types=1);

namespace App\Application\Processing;

use App\Entity\AuditEvent;
use App\Entity\CockpitState;
use App\Entity\DeviceEvent;
use App\Entity\ProductionEvent;
use App\Repository\CockpitModelMappingRepository;
use App\Repository\CockpitStateRepository;
use App\Repository\ProductionEventRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

class Scanner1ProductionProcessor
{
    private EntityManagerInterface $em;
    private CockpitModelMappingRepository $mappingRepo;
    private CockpitStateRepository $cockpitStateRepo;
    private ProductionEventRepository $productionEventRepo;
    private LoggerInterface $logger;
    private int $batchSize;

    public function __construct(
        EntityManagerInterface $em,
        CockpitModelMappingRepository $mappingRepo,
        CockpitStateRepository $cockpitStateRepo,
        ProductionEventRepository $productionEventRepo,
        LoggerInterface $deviceIngestionLogger,
        #[Autowire(env: 'int:APP_PRODUCTION_BATCH_SIZE')]
        int $batchSize = 10,
    ) {
        $this->em = $em;
        $this->mappingRepo = $mappingRepo;
        $this->cockpitStateRepo = $cockpitStateRepo;
        $this->productionEventRepo = $productionEventRepo;
        $this->logger = $deviceIngestionLogger;
        $this->batchSize = $batchSize;
    }

    public function process(DeviceEvent $event): void
    {
        if ($event->getSourceType() !== 'scanner1') {
            return;
        }

        // Idempotency: skip if already processed
        $existingProd = $this->productionEventRepo->findOneBy(['device_event' => $event]);
        if ($existingProd) {
            $this->logger->info('Scanner1 event already processed. Skipping to enforce idempotency.', [
                'device_event_id' => $event->getId(),
            ]);
            if ($event->getProcessingStatus() !== 'processed') {
                $event->setProcessingStatus('processed');
                $this->em->flush();
            }

            return;
        }

        $payload = $event->getRawPayload();
        $modelStr = $payload['model'] ?? null;
        $quantityStr = $payload['quantity'] ?? null;
        $dateTimeStr = $payload['scandatetime'] ?? null;

        // Validation
        if (!$modelStr) {
            $this->markAsFailed($event, 'UNKNOWN_MODEL', 'Missing model in scanner payload.');

            return;
        }

        if ($quantityStr === null || (int) $quantityStr !== $this->batchSize) {
            $this->markAsFailed($event, 'INVALID_BATCH_QUANTITY', \sprintf('Expected batch size %d, got %s', $this->batchSize, $quantityStr));

            return;
        }

        $this->em->beginTransaction();

        try {
            // Find Model Mapping
            $mapping = $this->mappingRepo->findOneBy(['scanner_model' => $modelStr]);
            if (!$mapping || !$mapping->isActive()) {
                $this->markAsFailed($event, 'UNKNOWN_MODEL', "Model '{$modelStr}' is not mapped or inactive.");
                $this->em->commit();

                return;
            }

            $cockpit = $mapping->getCockpit();

            // Find or Create CockpitState with Pessimistic Write Lock
            $query = $this->em->createQuery('SELECT cs FROM App\Entity\CockpitState cs WHERE cs.cockpit = :cockpit')
                ->setParameter('cockpit', $cockpit)
                ->setLockMode(LockMode::PESSIMISTIC_WRITE);

            $cockpitState = $query->getOneOrNullResult();

            if (!$cockpitState) {
                // Rare case where production arrives before PLC requests, mathematically valid.
                $cockpitState = new CockpitState();
                $cockpitState->setCockpit($cockpit);
                $this->em->persist($cockpitState);
                $this->em->flush();
                $cockpitState = $query->getOneOrNullResult();
            }

            // Perform Math
            $currentReq = (int) $cockpitState->getTotalRequested();
            $currentProd = (int) $cockpitState->getTotalProduced();

            $oldBal = $currentReq - $currentProd;

            $newProd = $currentProd + $this->batchSize;
            $newBal = $currentReq - $newProd;

            $cockpitState->setTotalProduced((string) $newProd);
            $cockpitState->setCurrentBalance((string) $newBal);
            $cockpitState->setUpdatedAt(new \DateTimeImmutable());

            // Create Production Ledger Entry
            $prodEvent = new ProductionEvent();
            $prodEvent->setProductionUuid(Uuid::v4()->toRfc4122());
            $prodEvent->setDeviceEvent($event);
            $prodEvent->setCockpit($cockpit);
            $prodEvent->setScannerModel($modelStr);
            $prodEvent->setQuantity($this->batchSize);
            $prodEvent->setReceivedAt($event->getReceivedAt());

            if ($dateTimeStr) {
                $deviceTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeStr);
                if ($deviceTime) {
                    $prodEvent->setDeviceTimestamp($deviceTime);
                }
            }

            $now = new \DateTimeImmutable();
            $prodEvent->setProcessedAt($now);

            $this->em->persist($prodEvent);

            // FIFO Queue Integration (Phase 7)
            if ($oldBal > 0 && $newBal <= 0) {
                // Shortage resolved. Complete the active queue entry.
                $query = $this->em->createQuery('SELECT q FROM App\Entity\ProductionQueue q WHERE q.cockpit = :cockpit AND q.status IN (:statuses)')
                    ->setParameter('cockpit', $cockpit)
                    ->setParameter('statuses', ['pending', 'selected', 'in_production'])
                    ->setLockMode(LockMode::PESSIMISTIC_WRITE); // Ensure we lock it securely

                $queue = $query->getOneOrNullResult();
                if ($queue) {
                    $queue->setStatus('completed');
                    $queue->setCompletedAt($now);
                    $queue->setUpdatedAt($now);

                    // Audit Queue Exit
                    $queueAudit = new AuditEvent();
                    $queueAudit->setEventType('FIFO_RESOLVED');
                    $queueAudit->setDescription('Cockpit shortage resolved, exited active FIFO queue.');
                    $queueAudit->setContext([
                        'cockpit' => $cockpit->getCockpitCode(),
                        'queue_uuid' => $queue->getQueueUuid(),
                        'device_event_id' => $event->getId(),
                    ]);
                    $this->em->persist($queueAudit);
                }
            }

            // Mark device event processed
            $event->setProcessingStatus('processed');
            $event->setProcessedAt($now);

            // Audit
            $audit = new AuditEvent();
            $audit->setEventType('TROLLEY_PRODUCTION_ACCEPTED');
            $audit->setDescription('Processed Scanner1 event and updated production balance.');
            $audit->setContext([
                'device_event_id' => $event->getId(),
                'production_uuid' => $prodEvent->getProductionUuid(),
                'cockpit' => $cockpit->getCockpitCode(),
                'new_produced' => $cockpitState->getTotalProduced(),
                'new_balance' => $cockpitState->getCurrentBalance(),
            ]);
            $this->em->persist($audit);

            $this->em->flush();
            $this->em->commit();

            $this->logger->info('Trolley production successfully processed.', [
                'device_event_id' => $event->getId(),
                'cockpit' => $cockpit->getCockpitCode(),
                'new_balance' => $cockpitState->getCurrentBalance(),
            ]);
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Transaction failed during Scanner1 processing.', [
                'error' => $e->getMessage(),
                'device_event_id' => $event->getId(),
            ]);

            $this->markAsFailed($event, 'PROCESSING_ERROR', $e->getMessage(), true);

            throw $e;
        }
    }

    private function markAsFailed(DeviceEvent $event, string $reason, string $details, bool $newTransaction = false): void
    {
        if ($newTransaction && !$this->em->isOpen()) {
            return;
        }

        $event->setProcessingStatus('failed');
        $event->setLastError($reason . ': ' . $details);

        $audit = new AuditEvent();
        $audit->setEventType('TROLLEY_PRODUCTION_REJECTED');
        $audit->setDescription($reason);
        $audit->setContext([
            'device_event_id' => $event->getId(),
            'details' => $details,
        ]);

        $this->em->persist($audit);
        $this->em->flush();

        $this->logger->warning('Scanner1 Production Failed/Rejected.', [
            'device_event_id' => $event->getId(),
            'reason' => $reason,
        ]);
    }
}
