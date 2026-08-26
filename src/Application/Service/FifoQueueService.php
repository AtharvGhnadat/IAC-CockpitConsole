<?php

namespace App\Application\Service;

use App\Entity\ProductionQueue;
use App\Entity\AuditEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;

class FifoQueueService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get the active production queue entry, if any.
     */
    public function getCurrentProduction(): ?ProductionQueue
    {
        return $this->em->getRepository(ProductionQueue::class)
            ->findOneBy(['status' => 'in_production']);
    }

    /**
     * Returns all pending/selected items ordered strictly by FIFO.
     * @return ProductionQueue[]
     */
    public function getPendingQueue(): array
    {
        return $this->em->createQuery('
            SELECT q FROM App\Entity\ProductionQueue q 
            WHERE q.status IN (:statuses)
            ORDER BY q.pending_device_timestamp ASC, 
                     q.pending_received_at ASC, 
                     q.pending_event_id ASC
        ')
        ->setParameter('statuses', ['pending', 'selected'])
        ->getResult();
    }

    /**
     * Starts the next production based on FIFO deterministic ordering.
     * Transactional. Throws exception if one is already in production.
     */
    public function startNextProduction(string $triggerSource = 'system'): ?ProductionQueue
    {
        $this->em->beginTransaction();
        try {
            // Lock the current in_production to prevent concurrent starts
            $currentQuery = $this->em->createQuery('
                SELECT q FROM App\Entity\ProductionQueue q 
                WHERE q.status = :status
            ')
            ->setParameter('status', 'in_production')
            ->setLockMode(LockMode::PESSIMISTIC_WRITE);
            
            $current = $currentQuery->getOneOrNullResult();
            
            if ($current) {
                // A cockpit is already in production. Do not preempt it.
                $this->em->rollback();
                throw new \RuntimeException('Production is already active for cockpit: ' . $current->getCockpit()->getCockpitCode());
            }

            // Find the NEXT cockpit in the queue, locking it.
            $nextQuery = $this->em->createQuery('
                SELECT q FROM App\Entity\ProductionQueue q 
                WHERE q.status IN (:statuses)
                ORDER BY q.pending_device_timestamp ASC, 
                         q.pending_received_at ASC, 
                         q.pending_event_id ASC
            ')
            ->setParameter('statuses', ['pending', 'selected'])
            ->setMaxResults(1)
            ->setLockMode(LockMode::PESSIMISTIC_WRITE);

            $nextQueue = $nextQuery->getOneOrNullResult();

            if (!$nextQueue) {
                // Queue is empty.
                $this->em->rollback();
                return null;
            }

            $now = new \DateTimeImmutable();
            
            // Log old status before changing
            $oldStatus = $nextQueue->getStatus();

            $nextQueue->setStatus('in_production');
            $nextQueue->setStartedAt($now);
            $nextQueue->setUpdatedAt($now);
            $nextQueue->setSelectedAt($now); // Just in case it wasn't selected

            // Audit
            $audit = new AuditEvent();
            $audit->setEventType('PRODUCTION_STARTED');
            $audit->setDescription('FIFO Queue selected for production.');
            $audit->setContext([
                'cockpit' => $nextQueue->getCockpit()->getCockpitCode(),
                'queue_uuid' => $nextQueue->getQueueUuid(),
                'trigger_source' => $triggerSource,
                'previous_status' => $oldStatus
            ]);
            $this->em->persist($audit);

            $this->em->flush();
            $this->em->commit();

            return $nextQueue;

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
