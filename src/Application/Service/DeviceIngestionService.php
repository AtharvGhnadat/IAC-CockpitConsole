<?php

namespace App\Application\Service;

use App\Application\Device\Validator\DevicePayloadValidatorInterface;
use App\Application\Device\Validator\EsslPayloadValidator;
use App\Application\Device\Validator\PlcPayloadValidator;
use App\Application\Device\Validator\Scanner1PayloadValidator;
use App\Application\Device\Validator\Scanner2PayloadValidator;
use App\Application\DTO\DeviceEventEnvelope;
use App\Entity\DeviceEvent;
use App\Infrastructure\Persistence\RawDeviceEventRecorder;
use App\Repository\DeviceRepository;
use Psr\Log\LoggerInterface;

class DeviceIngestionService
{
    private RawDeviceEventRecorder $recorder;
    private DeviceRepository $deviceRepository;
    private LoggerInterface $logger;
    /** @var array<string, DevicePayloadValidatorInterface> */
    private array $validators;

    public function __construct(
        RawDeviceEventRecorder $recorder,
        DeviceRepository $deviceRepository,
        LoggerInterface $deviceIngestionLogger,
        EsslPayloadValidator $esslValidator,
        PlcPayloadValidator $plcValidator,
        Scanner1PayloadValidator $scanner1Validator,
        Scanner2PayloadValidator $scanner2Validator
    ) {
        $this->recorder = $recorder;
        $this->deviceRepository = $deviceRepository;
        $this->logger = $deviceIngestionLogger;
        
        $this->validators = [
            'essl' => $esslValidator,
            'plc' => $plcValidator,
            'scanner1' => $scanner1Validator,
            'scanner2' => $scanner2Validator,
        ];
    }

    public function ingest(string $sourceType, string $rawJson, ?string $sourceIp): DeviceEvent
    {
        $this->logger->info(sprintf('Received ingestion request for source: %s', $sourceType), [
            'source_ip' => $sourceIp
        ]);

        // 1. Basic JSON validation
        $payload = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON received.', [
                'source_type' => $sourceType,
                'source_ip' => $sourceIp,
                'raw_body' => $rawJson,
                'json_error' => json_last_error_msg()
            ]);
            throw new \InvalidArgumentException('Malformed JSON payload.');
        }

        // 2. Resolve specific validator
        if (!isset($this->validators[$sourceType])) {
            $this->logger->error('Unsupported source type.', [
                'source_type' => $sourceType,
                'source_ip' => $sourceIp
            ]);
            throw new \InvalidArgumentException(sprintf('Unsupported source type: %s', $sourceType));
        }
        $validator = $this->validators[$sourceType];

        // 3. Structural validation
        try {
            $validator->validateStructure($payload);
            $deviceTimestamp = $validator->extractTimestamp($payload);
            $deviceIdentifier = $validator->extractDeviceIdentifier($payload);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Structural validation failed.', [
                'source_type' => $sourceType,
                'source_ip' => $sourceIp,
                'raw_body' => $rawJson,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        // 4. Resolve device
        $device = null;
        if ($deviceIdentifier) {
            $device = $this->deviceRepository->findActiveDeviceByCode($deviceIdentifier);
            if (!$device) {
                $this->logger->warning('Unknown device identity.', [
                    'source_type' => $sourceType,
                    'device_identifier' => $deviceIdentifier,
                    'source_ip' => $sourceIp
                ]);
                // Requirement: do not crash. Preserve the event with a nullable device relation.
            }
        }

        // 5. Create DTO & Persist
        $envelope = new DeviceEventEnvelope(
            $sourceType,
            $payload, // The decoded array, RawDeviceEventRecorder will hash and re-encode deterministically
            $sourceIp,
            $deviceTimestamp
        );

        try {
            $event = $this->recorder->recordEvent($envelope, $device);
            
            $this->logger->info('Device event durably persisted.', [
                'event_uuid' => $event->getEventUuid(),
                'source_type' => $sourceType,
                'event_id' => $event->getId()
            ]);
            
            return $event;
            
        } catch (\Exception $e) {
            $this->logger->critical('Database persistence failed for device event.', [
                'source_type' => $sourceType,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ]);
            throw new \RuntimeException('Failed to persist device event.');
        }
    }
}
