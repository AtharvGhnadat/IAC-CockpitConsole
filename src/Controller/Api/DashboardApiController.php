<?php

namespace App\Controller\Api;

use App\Application\Service\DashboardSnapshotService;
use App\Entity\DashboardColumn;
use App\Entity\DashboardRow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardApiController extends AbstractController
{
    private DashboardSnapshotService $snapshotService;
    private EntityManagerInterface $em;

    public function __construct(DashboardSnapshotService $snapshotService, EntityManagerInterface $em)
    {
        $this->snapshotService = $snapshotService;
        $this->em = $em;
    }

    #[Route('/snapshot', name: 'api_dashboard_snapshot', methods: ['GET'])]
    public function snapshot(): JsonResponse
    {
        return $this->json($this->snapshotService->getSnapshot());
    }

    #[Route('/config', name: 'api_dashboard_config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        $rows = $this->em->getRepository(DashboardRow::class)->findBy(['is_visible' => true], ['display_order' => 'ASC']);
        
        $config = [];
        foreach ($rows as $row) {
            $cols = [];
            foreach ($row->getDashboardColumns() as $col) {
                if ($col->isVisible()) {
                    $cols[] = [
                        'id' => $col->getId(),
                        'name' => $col->getName(),
                        'metric_key' => $col->getMetricKey(),
                        'cockpit_id' => $col->getCockpit() ? $col->getCockpit()->getId() : null,
                        'display_order' => $col->getDisplayOrder()
                    ];
                }
            }
            $config[] = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'display_order' => $row->getDisplayOrder(),
                'columns' => $cols
            ];
        }

        return $this->json($config);
    }

    #[Route('/row', name: 'api_dashboard_row_add', methods: ['POST'])]
    public function addRow(Request $request): JsonResponse
    {
        // Require Admin role checking conceptually
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $row = new DashboardRow();
        $row->setName($data['name']);
        
        // simple order resolution
        $maxOrder = $this->em->createQuery('SELECT MAX(r.display_order) FROM App\Entity\DashboardRow r')->getSingleScalarResult();
        $row->setDisplayOrder((int)$maxOrder + 1);

        $this->em->persist($row);
        $this->em->flush();

        return $this->json(['id' => $row->getId(), 'name' => $row->getName()], 201);
    }

    #[Route('/row/{id}', name: 'api_dashboard_row_delete', methods: ['DELETE'])]
    public function deleteRow(int $id): JsonResponse
    {
        $row = $this->em->getRepository(DashboardRow::class)->find($id);
        if (!$row) {
            return $this->json(['error' => 'Row not found'], 404);
        }

        $this->em->remove($row);
        $this->em->flush();

        return $this->json(['status' => 'deleted']);
    }

    #[Route('/column', name: 'api_dashboard_column_add', methods: ['POST'])]
    public function addColumn(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['dashboard_row_id']) || !isset($data['name']) || !isset($data['metric_key'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $row = $this->em->getRepository(DashboardRow::class)->find($data['dashboard_row_id']);
        if (!$row) return $this->json(['error' => 'Row not found'], 404);

        $col = new DashboardColumn();
        $col->setDashboardRow($row);
        $col->setName($data['name']);
        $col->setMetricKey($data['metric_key']);
        
        if (isset($data['cockpit_id'])) {
            $cockpit = $this->em->getRepository(\App\Entity\Cockpit::class)->find($data['cockpit_id']);
            if ($cockpit) {
                $col->setCockpit($cockpit);
            }
        }

        $maxOrder = $this->em->createQuery('SELECT MAX(c.display_order) FROM App\Entity\DashboardColumn c WHERE c.dashboardRow = :r')
            ->setParameter('r', $row)
            ->getSingleScalarResult();
        
        $col->setDisplayOrder((int)$maxOrder + 1);

        $this->em->persist($col);
        $this->em->flush();

        return $this->json(['id' => $col->getId(), 'name' => $col->getName()], 201);
    }
}
