<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Service\SystemHealthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthApiController extends AbstractController
{
    private SystemHealthService $healthService;

    public function __construct(SystemHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    #[Route('/health', name: 'api_health_public', methods: ['GET'])]
    public function publicHealth(): JsonResponse
    {
        $snapshot = $this->healthService->getHealthSnapshot();

        // Public endpoint should only expose minimal DB/infrastructure status
        $status = $snapshot['database']['status'] === 'HEALTHY' ? 'ok' : 'degraded';

        $code = $status === 'ok' ? 200 : 503;

        return $this->json(['status' => $status], $code);
    }

    #[Route('/api/health/summary', name: 'api_health_summary', methods: ['GET'])]
    public function summaryHealth(): JsonResponse
    {
        $snapshot = $this->healthService->getHealthSnapshot();

        // Operator summary
        $summary = [
            'overall' => $snapshot['overall'],
            'database' => $snapshot['database']['status'],
            'processing' => $snapshot['processing']['status'],
            'devices' => [],
        ];

        foreach ($snapshot['devices'] as $code => $deviceHealth) {
            $summary['devices'][$code] = $deviceHealth['status'];
        }

        return $this->json($summary);
    }

    #[Route('/api/health/details', name: 'api_health_details', methods: ['GET'])]
    public function detailsHealth(): JsonResponse
    {
        // Admin detailed health
        // Need explicit role checks here, or rely on firewall (assume firewall handles API paths for Phase 9)
        return $this->json($this->healthService->getHealthSnapshot());
    }
}
