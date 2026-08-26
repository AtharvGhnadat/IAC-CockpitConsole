<?php

namespace App\Application\Service;

use App\Repository\CockpitStateRepository;
use App\Repository\ProductionQueueRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardSnapshotService
{
    private EntityManagerInterface $em;
    private SystemHealthService $healthService;
    private CockpitStateRepository $cockpitStateRepo;
    private ProductionQueueRepository $queueRepo;

    public function __construct(
        EntityManagerInterface $em,
        SystemHealthService $healthService,
        CockpitStateRepository $cockpitStateRepo,
        ProductionQueueRepository $queueRepo
    ) {
        $this->em = $em;
        $this->healthService = $healthService;
        $this->cockpitStateRepo = $cockpitStateRepo;
        $this->queueRepo = $queueRepo;
    }

    public function getSnapshot(): array
    {
        $now = new \DateTimeImmutable();
        
        $snapshot = [
            'generated_at' => $now->format('c'),
            'metrics' => []
        ];

        try {
            // Overall Production Summaries
            $conn = $this->em->getConnection();
            $totals = $conn->fetchAssociative('
                SELECT 
                    SUM(total_requested) as overall_requested,
                    SUM(total_produced) as overall_produced,
                    SUM(total_dispatched) as overall_dispatched,
                    SUM(available_stock) as overall_available
                FROM cockpit_state
            ');
            
            $snapshot['metrics']['OVERALL_REQUESTED'] = (int) ($totals['overall_requested'] ?? 0);
            $snapshot['metrics']['OVERALL_PRODUCED'] = (int) ($totals['overall_produced'] ?? 0);
            $snapshot['metrics']['OVERALL_DISPATCHED'] = (int) ($totals['overall_dispatched'] ?? 0);
            $snapshot['metrics']['OVERALL_AVAILABLE'] = (int) ($totals['overall_available'] ?? 0);

            // Cockpit Specific States
            $states = $this->cockpitStateRepo->findAll();
            foreach ($states as $state) {
                $cId = $state->getCockpit()->getId();
                $snapshot['metrics']["COCKPIT_REQUESTED_{$cId}"] = $state->getTotalRequested();
                $snapshot['metrics']["COCKPIT_PRODUCED_{$cId}"] = $state->getTotalProduced();
                $snapshot['metrics']["COCKPIT_DISPATCHED_{$cId}"] = $state->getTotalDispatched();
                $snapshot['metrics']["COCKPIT_AVAILABLE_{$cId}"] = $state->getAvailableStock();
                $snapshot['metrics']["COCKPIT_BALANCE_{$cId}"] = $state->getCurrentBalance();
            }

            // FIFO Metrics
            $current = $this->queueRepo->findCurrentProductionCockpit();
            $snapshot['metrics']['FIFO_CURRENT'] = $current ? $current->getCockpit()->getCockpitName() : 'None';
            
            $next = $this->queueRepo->findOneBy(
                ['status' => 'pending'],
                ['created_at' => 'ASC']
            );
            $snapshot['metrics']['FIFO_NEXT'] = $next ? $next->getCockpit()->getCockpitName() : 'None';
            
            $queueCount = $this->queueRepo->count(['status' => 'pending']);
            $snapshot['metrics']['FIFO_QUEUE_SIZE'] = $queueCount;

            // Raw Queue data for the UI to display the full queue if desired
            $queueItems = $this->queueRepo->findBy(['status' => 'pending'], ['created_at' => 'ASC'], 10);
            $snapshot['queue'] = [];
            foreach ($queueItems as $q) {
                $ageMinutes = (int) floor((time() - $q->getCreatedAt()->getTimestamp()) / 60);
                $snapshot['queue'][] = [
                    'cockpit' => $q->getCockpit()->getCockpitName(),
                    'waiting_minutes' => $ageMinutes
                ];
            }

            // System Health Metrics
            $health = $this->healthService->getHealthSnapshot();
            $snapshot['metrics']['HEALTH_OVERALL'] = $health['overall'];
            $snapshot['metrics']['HEALTH_PLC'] = $health['devices']['PLC']['status'] ?? 'UNKNOWN';
            $snapshot['metrics']['HEALTH_SCANNER1'] = $health['devices']['SCANNER1']['status'] ?? 'UNKNOWN';
            $snapshot['metrics']['HEALTH_SCANNER2'] = $health['devices']['SCANNER2']['status'] ?? 'UNKNOWN';
            $snapshot['metrics']['HEALTH_ESSL'] = $health['devices']['ESSL']['status'] ?? 'UNKNOWN';

        } catch (\Exception $e) {
            $snapshot['metrics']['HEALTH_OVERALL'] = 'CRITICAL';
            $snapshot['error'] = 'Failed to generate full snapshot';
        }

        return $snapshot;
    }
}
