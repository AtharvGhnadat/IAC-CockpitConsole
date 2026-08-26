<?php

namespace App\Entity;

use App\Repository\DispatchEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DispatchEventRepository::class)]
#[ORM\Table(name: 'dispatch_events')]
class DispatchEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $dispatch_uuid = null;

    #[ORM\OneToOne(targetEntity: DeviceEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeviceEvent $device_event = null;

    #[ORM\ManyToOne(targetEntity: Cockpit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cockpit $cockpit = null;

    #[ORM\Column(length: 255)]
    private ?string $scanner_model = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $device_timestamp = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $received_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
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

    public function getDispatchUuid(): ?string
    {
        return $this->dispatch_uuid;
    }

    public function setDispatchUuid(string $dispatch_uuid): static
    {
        $this->dispatch_uuid = $dispatch_uuid;
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

    public function getScannerModel(): ?string
    {
        return $this->scanner_model;
    }

    public function setScannerModel(string $scanner_model): static
    {
        $this->scanner_model = $scanner_model;
        return $this;
    }

    public function getQuantity(): ?int
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

    public function setProcessedAt(\DateTimeImmutable $processed_at): static
    {
        $this->processed_at = $processed_at;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
}
