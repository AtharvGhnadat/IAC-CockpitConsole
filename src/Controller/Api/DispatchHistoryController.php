<?php

namespace App\Controller\Api;

use App\Repository\DispatchEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dispatch')]
class DispatchHistoryController extends AbstractController
{
    private DispatchEventRepository $dispatchRepo;

    public function __construct(DispatchEventRepository $dispatchRepo)
    {
        $this->dispatchRepo = $dispatchRepo;
    }

    #[Route('/recent', name: 'api_dispatch_recent', methods: ['GET'])]
    public function getRecent(): JsonResponse
    {
        $events = $this->dispatchRepo->createQueryBuilder('d')
            ->orderBy('d.processed_at', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'model' => $event->getScannerModel(),
                'cockpit' => $event->getCockpit()->getCockpitCode(),
                'quantity' => $event->getQuantity(),
                'scan_time' => $event->getDeviceTimestamp()?->format(\DateTimeInterface::ATOM),
                'received_time' => $event->getReceivedAt()->format(\DateTimeInterface::ATOM),
                'processed_time' => $event->getProcessedAt()->format(\DateTimeInterface::ATOM)
            ];
        }

        return $this->json($data);
    }
}
