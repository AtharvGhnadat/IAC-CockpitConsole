<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Service\FifoQueueService;
use App\Repository\CockpitStateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/production')]
class ProductionQueueController extends AbstractController
{
    private FifoQueueService $fifoService;
    private CockpitStateRepository $stateRepo;

    public function __construct(FifoQueueService $fifoService, CockpitStateRepository $stateRepo)
    {
        $this->fifoService = $fifoService;
        $this->stateRepo = $stateRepo;
    }

    #[Route('/queue', name: 'api_production_queue', methods: ['GET'])]
    public function getQueue(): JsonResponse
    {
        $current = $this->fifoService->getCurrentProduction();
        $pending = $this->fifoService->getPendingQueue();

        $now = new \DateTimeImmutable();

        $queueData = [];
        $nextData = null;

        foreach ($pending as $index => $q) {
            $cockpit = $q->getCockpit();
            $state = $this->stateRepo->findOneBy(['cockpit' => $cockpit]);

            // Calculate waiting seconds dynamically
            $pendingSince = $q->getPendingReceivedAt();
            $waitingSeconds = $now->getTimestamp() - $pendingSince->getTimestamp();

            $item = [
                'position' => $index + 1,
                'cockpit' => $cockpit->getCockpitCode(),
                'current_balance' => $state ? (int) $state->getCurrentBalance() : 0,
                'waiting_seconds' => max(0, $waitingSeconds),
            ];

            $queueData[] = $item;

            if ($index === 0) {
                $nextData = [
                    'cockpit' => $cockpit->getCockpitCode(),
                    'waiting_seconds' => max(0, $waitingSeconds),
                ];
            }
        }

        $currentData = null;
        if ($current) {
            $currentData = [
                'cockpit' => $current->getCockpit()->getCockpitCode(),
                'status' => $current->getStatus(),
            ];
        }

        return $this->json([
            'current' => $currentData,
            'next' => $nextData,
            'queue' => $queueData,
        ]);
    }

    #[Route('/start-next', name: 'api_production_start_next', methods: ['POST'])]
    public function startNext(Request $request): JsonResponse
    {
        // In a real application, require human authentication here.
        // E.g., $this->denyAccessUnlessGranted('ROLE_OPERATOR');

        try {
            $started = $this->fifoService->startNextProduction('api_operator');

            if (!$started) {
                return $this->json(['message' => 'No pending cockpits in queue.'], 404);
            }

            return $this->json([
                'message' => 'Production started successfully.',
                'cockpit' => $started->getCockpit()->getCockpitCode(),
            ]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
