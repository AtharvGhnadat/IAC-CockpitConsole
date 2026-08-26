<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Service\DeviceIngestionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DeviceReceiverController extends AbstractController
{
    private DeviceIngestionService $ingestionService;

    public function __construct(DeviceIngestionService $ingestionService)
    {
        $this->ingestionService = $ingestionService;
    }

    #[Route('/api/device/{sourceType}', name: 'api_device_receive', methods: ['POST'])]
    public function receive(string $sourceType, Request $request): JsonResponse
    {
        // 1. Content-Type check
        if ($request->getContentTypeFormat() !== 'json') {
            return $this->json([
                'success' => false,
                'error' => 'UNSUPPORTED_CONTENT_TYPE',
                'message' => 'Expected application/json',
            ], 415);
        }

        $rawBody = $request->getContent();

        // 2. Request size check (e.g., 16KB max for industrial tiny JSONs)
        if (\strlen($rawBody) > 16384) {
            return $this->json([
                'success' => false,
                'error' => 'PAYLOAD_TOO_LARGE',
            ], 413);
        }

        $sourceIp = $request->getClientIp();

        // 3. Delegate to ingestion service
        try {
            $event = $this->ingestionService->ingest($sourceType, $rawBody, $sourceIp);

            return $this->json([
                'success' => true,
                'event_id' => $event->getId(),
                'event_uuid' => $event->getEventUuid(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => 'INVALID_PAYLOAD',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'error' => 'PERSISTENCE_FAILURE',
                'message' => 'Failed to securely store the event',
            ], 500);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
