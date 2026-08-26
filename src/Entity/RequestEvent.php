<?php

namespace App\Entity;

use App\Repository\RequestEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestEventRepository::class)]
#[ORM\Table(name: 'request_events')]
#[ORM\Index(name: 'idx_device_timestamp', columns: ['device_timestamp'])]
#[ORM\Index(name: 'idx_received_at', columns: ['received_at'])]
#[ORM\Index(name: 'idx_created_at', columns: ['created_at'])]
class RequestEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $request_uuid = null;

    #[ORM\ManyToOne(targetEntity: DeviceEvent::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?DeviceEvent $device_event = null;

    #[ORM\ManyToOne(targetEntity: Cockpit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cockpit $cockpit = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $device_timestamp = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP(6)'])]
    private ?\DateTimeImmutable $received_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $processed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRequestUuid(): ?string
    {
        return $this->request_uuid;
    }

    public function setRequestUuid(string $request_uuid): static
    {
        $this->request_uuid = $request_uuid;
        return $this;
    }

    public function getDeviceEvent(): ?DeviceEvent
    {
        return $this->device_event;
    }

    public function setDeviceEvent(DeviceEvent $device_event): static
    {
        $this->device_event = $device_event;
        return $this;
    }

    public function getCockpit(): ?Cockpit
    {
        return $this->cockpit;
    }

    public function setCockpit(?Cockpit $cockpit): static
    {
        $this->cockpit = $cockpit;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
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

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processed_at;
    }

    public function setProcessedAt(?\DateTimeImmutable $processed_at): static
    {
        $this->processed_at = $processed_at;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
}
