<?php

namespace App\Application\Service;

use App\Application\Device\Validator\DevicePayloadValidatorInterface;
use App\Application\Device\Validator\EsslPayloadValidator;
use App\Application\Device\Validator\PlcPayloadValidator;
use App\Application\Device\Validator\Scanner1PayloadValidator;
use App\Application\Device\Validator\Scanner2PayloadValidator;
use App\Application\DTO\DeviceEventEnvelope;
use App\Entity\DeviceEvent;
use App\Entity\DeviceHealth;
use App\Entity\Device;
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

        $now = new \DateTimeImmutable();
        
        // Setup default device based on source type to allow health tracking
        $deviceIdentifier = strtoupper($sourceType);
        $device = $this->deviceRepository->findActiveDeviceByCode($deviceIdentifier);
        
        if (!$device) {
            $device = new Device();
            $device->setDeviceCode($deviceIdentifier);
            $device->setDeviceType($sourceType);
            $device->setIpAddress($sourceIp);
            $this->deviceRepository->save($device, true);
            
            // Also initialize health for the new device
            $health = new DeviceHealth();
            $health->setDevice($device);
            $this->deviceRepository->getEntityManager()->persist($health);
            $this->deviceRepository->getEntityManager()->flush();
        }

        // 1. Update last_seen_at
        $healthRepo = $this->deviceRepository->getEntityManager()->getRepository(DeviceHealth::class);
        $health = $healthRepo->findOneBy(['device' => $device]);
        if ($health) {
            $health->setLastSeenAt($now);
            $health->setUpdatedAt($now);
            $this->deviceRepository->getEntityManager()->flush();
        }

        // 2. Basic JSON validation
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

        // 3. Resolve specific validator
        if (!isset($this->validators[$sourceType])) {
            $this->logger->error('Unsupported source type.', [
                'source_type' => $sourceType,
                'source_ip' => $sourceIp
            ]);
            throw new \InvalidArgumentException(sprintf('Unsupported source type: %s', $sourceType));
        }
        $validator = $this->validators[$sourceType];

        // 4. Structural validation
        try {
            $validator->validateStructure($payload);
            $deviceTimestamp = $validator->extractTimestamp($payload);
            // If the payload specifies a different device code (like specific scanner), we can use it, but typically we use the source type as the master device code
            // $deviceIdentifier = $validator->extractDeviceIdentifier($payload); 
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Structural validation failed.', [
                'source_type' => $sourceType,
                'source_ip' => $sourceIp,
                'raw_body' => $rawJson,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        // 5. Update last_valid_event_at
        if ($health) {
            $health->setLastValidEventAt($now);
            $health->setUpdatedAt($now);
            $this->deviceRepository->getEntityManager()->flush();
        }

        // 6. Create DTO & Persist
        $envelope = new DeviceEventEnvelope(
            $sourceType,
            $payload,
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
            
            // Process synchronously
            $processed = false;

            try {
                // Synchronous fingerprint processing for Phase 3
                if ($sourceType === 'essl' && $this->fingerprintProcessor) {
                    $this->fingerprintProcessor->process($event);
                    $processed = true;
                }
                
                // Synchronous process PLC events for Phase 5
                if ($sourceType === 'plc' && $this->plcProcessor) {
                    $this->plcProcessor->process($event);
                    $processed = true;
                }
                
                // Synchronously process Scanner1 events for Phase 6
                if ($sourceType === 'scanner1' && $this->scanner1Processor) {
                    $this->scanner1Processor->process($event);
                    $processed = true;
                }
                
                // Synchronously process Scanner2 events for Phase 8
                if ($sourceType === 'scanner2' && $this->scanner2Processor) {
                    $this->scanner2Processor->process($event);
                    $processed = true;
                }
                
                // 7. Check for failures and update health
                if ($health) {
                    if ($event->getProcessingStatus() === 'failed') {
                        $errorData = json_decode($event->getProcessingError() ?? '{}', true);
                        $health->setLastErrorAt($now);
                        $health->setLastErrorCode($errorData['code'] ?? 'UNKNOWN_ERROR');
                        $health->incrementConsecutiveFailures();
                        $health->setUpdatedAt($now);
                    } elseif ($processed) {
                        $health->setLastProcessedAt($now);
                        $health->resetConsecutiveFailures();
                        $health->setUpdatedAt($now);
                    }
                    $this->deviceRepository->getEntityManager()->flush();
                }
                
            } catch (\Exception $procEx) {
                $this->logger->error('Synchronous processing failed.', [
                    'error' => $procEx->getMessage(),
                    'source' => $sourceType
                ]);
                
                if ($health) {
                    $health->setLastErrorAt($now);
                    $health->setLastErrorCode('PROCESSING_EXCEPTION');
                    $health->incrementConsecutiveFailures();
                    $health->setUpdatedAt($now);
                    $this->deviceRepository->getEntityManager()->flush();
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
