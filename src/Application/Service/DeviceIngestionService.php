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
    private ?\App\Application\Security\FingerprintEventProcessor $fingerprintProcessor;
    private ?\App\Application\Processing\PlcRequestProcessor $plcProcessor;
    private ?\App\Application\Processing\Scanner1ProductionProcessor $scanner1Processor;
    private ?\App\Application\Processing\Scanner2DispatchProcessor $scanner2Processor;
    /** @var array<string, DevicePayloadValidatorInterface> */
    private array $validators;

    public function __construct(
        RawDeviceEventRecorder $recorder,
        DeviceRepository $deviceRepository,
        LoggerInterface $deviceIngestionLogger,
        EsslPayloadValidator $esslValidator,
        PlcPayloadValidator $plcValidator,
        Scanner1PayloadValidator $scanner1Validator,
        Scanner2PayloadValidator $scanner2Validator,
        ?\App\Application\Security\FingerprintEventProcessor $fingerprintProcessor = null,
        ?\App\Application\Processing\PlcRequestProcessor $plcProcessor = null,
        ?\App\Application\Processing\Scanner1ProductionProcessor $scanner1Processor = null,
        ?\App\Application\Processing\Scanner2DispatchProcessor $scanner2Processor = null
    ) {
        $this->recorder = $recorder;
        $this->deviceRepository = $deviceRepository;
        $this->logger = $deviceIngestionLogger;
        $this->fingerprintProcessor = $fingerprintProcessor;
        $this->plcProcessor = $plcProcessor;
        $this->scanner1Processor = $scanner1Processor;
        $this->scanner2Processor = $scanner2Processor;
        
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
            
            // Synchronously process fingerprint events for Phase 4 
            // since we don't have background workers running yet.
            if ($sourceType === 'essl' && $this->fingerprintProcessor) {
                try {
                    $this->fingerprintProcessor->process($event);
                } catch (\Exception $procEx) {
                    $this->logger->error('Synchronous fingerprint processing failed.', [
                        'error' => $procEx->getMessage()
                    ]);
                }
            }
            
            // Synchronously process PLC events for Phase 5
            if ($sourceType === 'plc' && $this->plcProcessor) {
                try {
                    $this->plcProcessor->process($event);
                } catch (\Exception $procEx) {
                    $this->logger->error('Synchronous PLC processing failed.', [
                        'error' => $procEx->getMessage()
                    ]);
                }
            }
            
            // Synchronously process Scanner1 events for Phase 6
            if ($sourceType === 'scanner1' && $this->scanner1Processor) {
                try {
                    $this->scanner1Processor->process($event);
                } catch (\Exception $procEx) {
                    $this->logger->error('Synchronous Scanner1 processing failed.', [
                        'error' => $procEx->getMessage()
                    ]);
                }
            }
            
            // Synchronously process Scanner2 events for Phase 8
            if ($sourceType === 'scanner2' && $this->scanner2Processor) {
                try {
                    $this->scanner2Processor->process($event);
                } catch (\Exception $procEx) {
                    $this->logger->error('Synchronous Scanner2 processing failed.', [
                        'error' => $procEx->getMessage()
                    ]);
                }
            }
            
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
