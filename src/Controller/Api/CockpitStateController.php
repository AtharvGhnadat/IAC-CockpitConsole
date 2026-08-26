<?php

namespace App\Controller\Api;

use App\Repository\CockpitRepository;
use App\Repository\CockpitStateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CockpitStateController extends AbstractController
{
    #[Route('/api/cockpits/{cockpitCode}/state', name: 'api_cockpit_state', methods: ['GET'])]
    public function getState(
        string $cockpitCode,
        CockpitRepository $cockpitRepo,
        CockpitStateRepository $cockpitStateRepo
    ): JsonResponse {
        $cockpit = $cockpitRepo->findOneBy(['cockpit_code' => $cockpitCode]);
        
        if (!$cockpit) {
            return $this->json(['error' => 'Cockpit not found'], 404);
        }

        $state = $cockpitStateRepo->findOneBy(['cockpit' => $cockpit]);

        return $this->json([
            'cockpit' => $cockpitCode,
            'total_requested' => $state ? (int) $state->getTotalRequested() : 0,
            'total_produced' => $state ? (int) $state->getTotalProduced() : 0,
            'current_balance' => $state ? (int) $state->getCurrentBalance() : 0
        ]);
    }
}
