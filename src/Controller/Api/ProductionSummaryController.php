<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/production')]
class ProductionSummaryController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/summary', name: 'api_production_summary', methods: ['GET'])]
    public function getSummary(): JsonResponse
    {
        // Calculate aggregate statistics safely.
        // We aggregate from cockpit_state for the most accurate current view.

        $result = $this->em->getConnection()->fetchAssociative('
            SELECT 
                COALESCE(SUM(total_requested), 0) as total_req,
                COALESCE(SUM(total_produced), 0) as total_prod,
                COALESCE(SUM(total_dispatched), 0) as total_disp,
                COALESCE(SUM(available_stock), 0) as avail_stock
            FROM cockpit_state
        ');

        return $this->json([
            'total_requested' => (int) ($result['total_req'] ?? 0),
            'total_produced' => (int) ($result['total_prod'] ?? 0),
            'total_dispatched' => (int) ($result['total_disp'] ?? 0),
            'available_stock' => (int) ($result['avail_stock'] ?? 0),
        ]);
    }
}
