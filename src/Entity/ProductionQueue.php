<?php

namespace App\Entity;

use App\Repository\ProductionQueueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionQueueRepository::class)]
#[ORM\Table(name: 'production_queue')]
#[ORM\Index(name: 'idx_fifo_ordering', columns: ['status', 'pending_device_timestamp', 'pending_received_at', 'pending_event_id'])]
class ProductionQueue
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $queue_uuid = null;

    #[ORM\ManyToOne(targetEntity: Cockpit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cockpit $cockpit = null;

    #[ORM\ManyToOne(targetEntity: RequestEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?RequestEvent $trigger_request_event = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $pending_device_timestamp = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $pending_received_at = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $pending_event_id = null;

    #[ORM\Column(length: 30)]
    private string $status = 'pending'; // pending, selected, in_production, completed, cancelled

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $entered_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $selected_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $started_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->entered_at = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getQueueUuid(): ?string
    {
        return $this->queue_uuid;
    }

    public function setQueueUuid(string $queue_uuid): static
    {
        $this->queue_uuid = $queue_uuid;
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

    public function getTriggerRequestEvent(): ?RequestEvent
    {
        return $this->trigger_request_event;
    }

    public function setTriggerRequestEvent(?RequestEvent $trigger_request_event): static
    {
        $this->trigger_request_event = $trigger_request_event;
        return $this;
    }

    public function getPendingDeviceTimestamp(): ?\DateTimeImmutable
    {
        return $this->pending_device_timestamp;
    }

    public function setPendingDeviceTimestamp(?\DateTimeImmutable $pending_device_timestamp): static
    {
        $this->pending_device_timestamp = $pending_device_timestamp;
        return $this;
    }

    public function getPendingReceivedAt(): ?\DateTimeImmutable
    {
        return $this->pending_received_at;
    }

    public function setPendingReceivedAt(\DateTimeImmutable $pending_received_at): static
    {
        $this->pending_received_at = $pending_received_at;
        return $this;
    }

    public function getPendingEventId(): ?string
    {
        return $this->pending_event_id;
    }

    public function setPendingEventId(string $pending_event_id): static
    {
        $this->pending_event_id = $pending_event_id;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getEnteredAt(): ?\DateTimeImmutable
    {
        return $this->entered_at;
    }

    public function setEnteredAt(\DateTimeImmutable $entered_at): static
    {
        $this->entered_at = $entered_at;
        return $this;
    }

    public function getSelectedAt(): ?\DateTimeImmutable
    {
        return $this->selected_at;
    }

    public function setSelectedAt(?\DateTimeImmutable $selected_at): static
    {
        $this->selected_at = $selected_at;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    public function setStartedAt(?\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?\DateTimeImmutable $completed_at): static
    {
        $this->completed_at = $completed_at;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
