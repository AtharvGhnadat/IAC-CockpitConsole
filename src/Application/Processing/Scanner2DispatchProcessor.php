<?php

declare(strict_types=1);

namespace App\Application\Processing;

use App\Entity\AuditEvent;
use App\Entity\CockpitModelMapping;
use App\Entity\CockpitState;
use App\Entity\DeviceEvent;
use App\Entity\DispatchEvent;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

class Scanner2DispatchProcessor
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private int $batchSize;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $deviceIngestionLogger,
        #[Autowire(env: 'int:APP_DISPATCH_BATCH_SIZE')] int $batchSize,
    ) {
        $this->em = $em;
        $this->logger = $deviceIngestionLogger;
        $this->batchSize = $batchSize;
    }

    public function process(DeviceEvent $event): void
    {
        if ($event->getSourceType() !== 'scanner2') {
            return;
        }

        if ($event->getProcessingStatus() === 'processed') {
            $this->logger->info('Scanner2 event already processed, skipping.', ['id' => $event->getId()]);

            return;
        }

        $payload = $event->getRawPayload();

        $modelStr = $payload['model'] ?? null;
        $quantityRaw = $payload['quantity'] ?? null;
        $dateTimeStr = $payload['scandatetime'] ?? null;

        $this->em->beginTransaction();

        try {
            // 1. Validate Quantity
            if (!is_numeric($quantityRaw) || (int) $quantityRaw !== $this->batchSize) {
                $this->markFailed($event, 'INVALID_DISPATCH_QUANTITY', 'Expected ' . $this->batchSize . ' got ' . $quantityRaw);
                $this->em->commit();

                return;
            }

            // 2. Resolve Model
            if (!$modelStr) {
                $this->markFailed($event, 'UNKNOWN_MODEL', 'Missing model string.');
                $this->em->commit();

                return;
            }

            $mapping = $this->em->getRepository(CockpitModelMapping::class)->findOneBy([
                'scanner_model' => $modelStr,
                'is_active' => true,
            ]);

            if (!$mapping) {
                $this->markFailed($event, 'UNKNOWN_MODEL', 'No active mapping for model: ' . $modelStr);
                $this->em->commit();

                return;
            }

            $cockpit = $mapping->getCockpit();

            // 3. Check Idempotency strictly
            $existing = $this->em->getRepository(DispatchEvent::class)->findOneBy(['device_event' => $event]);
            if ($existing) {
                $event->setProcessingStatus('processed');
                $event->setProcessedAt(new \DateTimeImmutable());
                $this->em->flush();
                $this->em->commit();

                return;
            }

            // 4. Lock CockpitState
            $cockpitStateQuery = $this->em->createQuery('SELECT cs FROM App\Entity\CockpitState cs WHERE cs.cockpit = :cockpit')
                ->setParameter('cockpit', $cockpit)
                ->setLockMode(LockMode::PESSIMISTIC_WRITE);

            $cockpitState = $cockpitStateQuery->getOneOrNullResult();

            if (!$cockpitState) {
                $this->markFailed($event, 'STATE_NOT_FOUND', 'Cockpit state missing for ' . $cockpit->getCockpitCode());
                $this->em->commit();

                return;
            }

            // 5. Verify Available Stock
            $currentAvailable = (int) $cockpitState->getAvailableStock();
            if ($currentAvailable < $this->batchSize) {
                // Not enough stock, leave event in 'received' or 'failed' for retry
                $this->markFailed($event, 'INSUFFICIENT_AVAILABLE_STOCK', \sprintf('Required %d, but only %d available.', $this->batchSize, $currentAvailable));
                $this->em->commit();

                return;
            }

            // 6. Perform Math
            $currentDispatched = (int) $cockpitState->getTotalDispatched();
            $newDispatched = $currentDispatched + $this->batchSize;
            $newAvailable = $currentAvailable - $this->batchSize;

            $cockpitState->setTotalDispatched((string) $newDispatched);
            $cockpitState->setAvailableStock((string) $newAvailable);
            $cockpitState->setUpdatedAt(new \DateTimeImmutable());

            // 7. Create Dispatch Ledger Entry
            $dispatchUuid = Uuid::v4()->toRfc4122();
            $dispatchEvent = new DispatchEvent();
            $dispatchEvent->setDispatchUuid($dispatchUuid);
            $dispatchEvent->setDeviceEvent($event);
            $dispatchEvent->setCockpit($cockpit);
            $dispatchEvent->setScannerModel($modelStr);
            $dispatchEvent->setQuantity($this->batchSize);
            $dispatchEvent->setReceivedAt($event->getReceivedAt());

            if ($dateTimeStr) {
                $deviceTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeStr);
                if ($deviceTime) {
                    $dispatchEvent->setDeviceTimestamp($deviceTime);
                }
            }

            $now = new \DateTimeImmutable();
            $dispatchEvent->setProcessedAt($now);

            $this->em->persist($dispatchEvent);

            // 8. Write Audit
            $audit = new AuditEvent();
            $audit->setEventType('DISPATCH_ACCEPTED');
            $audit->setDescription('Finished product successfully dispatched.');
            $audit->setContext([
                'cockpit' => $cockpit->getCockpitCode(),
                'dispatch_uuid' => $dispatchUuid,
                'device_event_id' => $event->getId(),
                'quantity' => $this->batchSize,
                'previous_available' => $currentAvailable,
                'new_available' => $newAvailable,
            ]);
            $this->em->persist($audit);

            // Mark device event processed
            $event->setProcessingStatus('processed');
            $event->setProcessedAt($now);

            $this->em->flush();
            $this->em->commit();

            $this->logger->info('Scanner2 event successfully dispatched.', [
                'event_id' => $event->getId(),
                'cockpit' => $cockpit->getCockpitCode(),
            ]);
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Transaction failed during Scanner2 processing', [
                'event_id' => $event->getId(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function markFailed(DeviceEvent $event, string $errorCode, string $details): void
    {
        $event->setProcessingStatus('failed');
        $event->setLastError(json_encode([
            'code' => $errorCode,
            'details' => $details,
        ]));

        // Save the failure state, use a separate flush to avoid transaction mess
        $this->em->flush();

        $this->logger->warning('Scanner2 event rejected', [
            'event_id' => $event->getId(),
            'code' => $errorCode,
            'details' => $details,
        ]);

        // Also log to audit if it's a business rejection
        if (\in_array($errorCode, ['INSUFFICIENT_AVAILABLE_STOCK', 'INVALID_DISPATCH_QUANTITY', 'UNKNOWN_MODEL'], true)) {
            $audit = new AuditEvent();
            $audit->setEventType('DISPATCH_REJECTED');
            $audit->setDescription('Dispatch was rejected during business validation.');
            $audit->setContext([
                'device_event_id' => $event->getId(),
                'reason' => $errorCode,
                'details' => $details,
            ]);
            $this->em->persist($audit);
            $this->em->flush();
        }
    }
}
