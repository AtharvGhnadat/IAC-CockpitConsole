<?php

namespace App\Entity;

use App\Repository\DeviceEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceEventRepository::class)]
#[ORM\Table(name: 'device_events')]
#[ORM\Index(name: 'idx_received_at', columns: ['received_at'])]
#[ORM\Index(name: 'idx_processing_status', columns: ['processing_status'])]
class DeviceEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null; // BigInt represented as string in PHP to avoid overflow

    #[ORM\Column(length: 36, unique: true)]
    private ?string $event_uuid = null;

    #[ORM\ManyToOne(targetEntity: Device::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Device $device = null;

    #[ORM\Column(length: 50)]
    private ?string $source_type = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $source_ip = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $device_timestamp = null;

    // We force the column to DATETIME(6) in schema for microseconds.
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP(6)'])]
    private ?\DateTimeImmutable $received_at = null;

    #[ORM\Column(type: Types::JSON)]
    private array $raw_payload = [];

    #[ORM\Column(length: 64)]
    private ?string $payload_hash = null;

    #[ORM\Column(length: 50)]
    private ?string $processing_status = 'received';

    #[ORM\Column]
    private int $processing_attempts = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processed_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $last_error = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        // Fallback for received_at if not explicitly set
        $this->received_at = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEventUuid(): ?string
    {
        return $this->event_uuid;
    }

    public function setEventUuid(string $event_uuid): static
    {
        $this->event_uuid = $event_uuid;
        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): static
    {
        $this->device = $device;
        return $this;
    }

    public function getSourceType(): ?string
    {
        return $this->source_type;
    }

    public function setSourceType(string $source_type): static
    {
        $this->source_type = $source_type;
        return $this;
    }

    public function getSourceIp(): ?string
    {
        return $this->source_ip;
    }

    public function setSourceIp(?string $source_ip): static
    {
        $this->source_ip = $source_ip;
        return $this;
    }

    public function getDeviceTimestamp(): ?\DateTimeImmutable
    {
        return $this->device_timestamp;
    }

    public function setDeviceTimestamp(?\DateTimeImmutable $device_timestamp): static
    {
        $this->device_timestamp = $device_timestamp;
        return $this;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->received_at;
    }

    public function setReceivedAt(\DateTimeImmutable $received_at): static
    {
        $this->received_at = $received_at;
        return $this;
    }

    public function getRawPayload(): array
    {
        return $this->raw_payload;
    }

    public function setRawPayload(array $raw_payload): static
    {
        $this->raw_payload = $raw_payload;
        return $this;
    }

    public function getPayloadHash(): ?string
    {
        return $this->payload_hash;
    }

    public function setPayloadHash(string $payload_hash): static
    {
        $this->payload_hash = $payload_hash;
        return $this;
    }

    public function getProcessingStatus(): ?string
    {
        return $this->processing_status;
    }

    public function setProcessingStatus(string $processing_status): static
    {
        $this->processing_status = $processing_status;
        return $this;
    }

    public function getProcessingAttempts(): int
    {
        return $this->processing_attempts;
    }

    public function setProcessingAttempts(int $processing_attempts): static
    {
        $this->processing_attempts = $processing_attempts;
        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processed_at;
    }

    public function setProcessedAt(?\DateTimeImmutable $processed_at): static
    {
        $this->processed_at = $processed_at;
        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->last_error;
    }

    public function setLastError(?string $last_error): static
    {
        $this->last_error = $last_error;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
}
