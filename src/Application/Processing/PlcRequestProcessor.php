<?php

declare(strict_types=1);

namespace App\Application\Processing;

use App\Entity\AuditEvent;
use App\Entity\CockpitState;
use App\Entity\DeviceEvent;
use App\Entity\RequestEvent;
use App\Repository\CockpitRepository;
use App\Repository\CockpitStateRepository;
use App\Repository\RequestEventRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class PlcRequestProcessor
{
    private EntityManagerInterface $em;
    private CockpitRepository $cockpitRepo;
    private CockpitStateRepository $cockpitStateRepo;
    private RequestEventRepository $requestEventRepo;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        CockpitRepository $cockpitRepo,
        CockpitStateRepository $cockpitStateRepo,
        RequestEventRepository $requestEventRepo,
        LoggerInterface $deviceIngestionLogger,
    ) {
        $this->em = $em;
        $this->cockpitRepo = $cockpitRepo;
        $this->cockpitStateRepo = $cockpitStateRepo;
        $this->requestEventRepo = $requestEventRepo;
        $this->logger = $deviceIngestionLogger;
    }

    public function process(DeviceEvent $event): void
    {
        if ($event->getSourceType() !== 'plc') {
            return;
        }

        // Idempotency: skip if already processed in RequestEvent ledger
        $existingRequest = $this->requestEventRepo->findOneBy(['device_event' => $event]);
        if ($existingRequest) {
            $this->logger->info('PLC event already processed. Skipping to enforce idempotency.', [
                'device_event_id' => $event->getId(),
            ]);
            // If it somehow failed midway before but request exists, ensure status is 'processed'
            if ($event->getProcessingStatus() !== 'processed') {
                $event->setProcessingStatus('processed');
                $this->em->flush();
            }

            return;
        }

        $payload = $event->getRawPayload();
        $cockpitCode = $payload['cockpit'] ?? null;
        $dateTimeStr = $payload['dateTime'] ?? null;

        if (!$cockpitCode) {
            $this->markAsFailed($event, 'UNKNOWN_COCKPIT', 'Missing cockpit code in PLC payload.');

            return;
        }

        $this->em->beginTransaction();

        try {
            // Find Cockpit Master
            $cockpit = $this->cockpitRepo->findOneBy(['cockpit_code' => $cockpitCode]);
            if (!$cockpit) {
                // Do not auto-create cockpits. Reject.
                $this->markAsFailed($event, 'UNKNOWN_COCKPIT', "Cockpit code '{$cockpitCode}' is not registered.");
                $this->em->commit();

                return;
            }

            // Find or Create CockpitState with Pessimistic Write Lock
            // We use DQL to ensure we can lock it immediately if it exists
            $query = $this->em->createQuery('SELECT cs FROM App\Entity\CockpitState cs WHERE cs.cockpit = :cockpit')
                ->setParameter('cockpit', $cockpit)
                ->setLockMode(LockMode::PESSIMISTIC_WRITE);

            $cockpitState = $query->getOneOrNullResult();

            if (!$cockpitState) {
                $cockpitState = new CockpitState();
                $cockpitState->setCockpit($cockpit);
                $this->em->persist($cockpitState);
                // Flush to ensure it's in DB for locking? Yes, flush early for new state
                $this->em->flush();
                // We re-query with lock to be absolutely certain (though new inserts are naturally isolated)
                $cockpitState = $query->getOneOrNullResult();
            }

            // Perform State Math
            $currentReq = (int) $cockpitState->getTotalRequested();
            $currentProd = (int) $cockpitState->getTotalProduced();

            $oldBal = $currentReq - $currentProd;

            $cockpitState->setTotalRequested((string) ($currentReq + 1));
            // Balance recalculation
            $newBal = ($currentReq + 1) - $currentProd;
            $cockpitState->setCurrentBalance((string) $newBal);
            $cockpitState->setUpdatedAt(new \DateTimeImmutable());

            // Create Request Ledger Entry
            $requestEvent = new RequestEvent();
            $requestEvent->setRequestUuid(Uuid::v4()->toRfc4122());
            $requestEvent->setDeviceEvent($event);
            $requestEvent->setCockpit($cockpit);
            $requestEvent->setQuantity(1);
            $requestEvent->setReceivedAt($event->getReceivedAt());

            if ($dateTimeStr) {
                $deviceTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeStr);
                if ($deviceTime) {
                    $requestEvent->setDeviceTimestamp($deviceTime);
                }
            }

            $now = new \DateTimeImmutable();
            $requestEvent->setProcessedAt($now);

            $this->em->persist($requestEvent);

            // FIFO Queue Integration (Phase 7)
            if ($oldBal <= 0 && $newBal > 0) {
                // Shortage just began. Create active queue entry.
                $queue = new \App\Entity\ProductionQueue();
                $queue->setQueueUuid(Uuid::v4()->toRfc4122());
                $queue->setCockpit($cockpit);
                $queue->setTriggerRequestEvent($requestEvent);
                $queue->setPendingDeviceTimestamp($requestEvent->getDeviceTimestamp());
                $queue->setPendingReceivedAt($requestEvent->getReceivedAt());
                $queue->setPendingEventId((string) $event->getId()); // Safe to use device_event_id for ordering tie-break

                $this->em->persist($queue);

                // Audit Queue Enter
                $queueAudit = new AuditEvent();
                $queueAudit->setEventType('FIFO_ENTERED');
                $queueAudit->setDescription('Cockpit shortage began, entered FIFO queue.');
                $queueAudit->setContext([
                    'cockpit' => $cockpitCode,
                    'queue_uuid' => $queue->getQueueUuid(),
                    'device_event_id' => $event->getId(),
                ]);
                $this->em->persist($queueAudit);
            }

            // Mark device event processed
            $event->setProcessingStatus('processed');
            $event->setProcessedAt($now);

            // Audit
            $audit = new AuditEvent();
            $audit->setEventType('PLC_REQUEST_ACCEPTED');
            $audit->setDescription('Processed PLC event and incremented cockpit balance.');
            $audit->setContext([
                'device_event_id' => $event->getId(),
                'request_uuid' => $requestEvent->getRequestUuid(),
                'cockpit' => $cockpitCode,
                'new_balance' => $cockpitState->getCurrentBalance(),
            ]);
            $this->em->persist($audit);

            $this->em->flush();
            $this->em->commit();

            $this->logger->info('PLC request successfully processed.', [
                'device_event_id' => $event->getId(),
                'cockpit' => $cockpitCode,
                'new_balance' => $cockpitState->getCurrentBalance(),
            ]);
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Transaction failed during PLC processing.', [
                'error' => $e->getMessage(),
                'device_event_id' => $event->getId(),
            ]);

            // Mark as failed outside of the rolled-back transaction
            $this->markAsFailed($event, 'PROCESSING_ERROR', $e->getMessage(), true);

            throw $e;
        }
    }

    private function markAsFailed(DeviceEvent $event, string $reason, string $details, bool $newTransaction = false): void
    {
        if ($newTransaction && !$this->em->isOpen()) {
            // If EM is closed from rollback, we can't reliably save.
            // In a real messenger context, the message goes to a failed queue.
            return;
        }

        $event->setProcessingStatus('failed');
        $event->setLastError($reason . ': ' . $details);

        $audit = new AuditEvent();
        $audit->setEventType('PLC_REQUEST_REJECTED');
        $audit->setDescription($reason);
        $audit->setContext([
            'device_event_id' => $event->getId(),
            'details' => $details,
        ]);

        $this->em->persist($audit);
        $this->em->flush();

        $this->logger->warning('PLC Request Failed/Rejected.', [
            'device_event_id' => $event->getId(),
            'reason' => $reason,
        ]);
    }
}
