<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DeviceHealthRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceHealthRepository::class)]
#[ORM\Table(name: 'device_health')]
class DeviceHealth
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Device::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Device $device = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $last_seen_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $last_valid_event_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $last_processed_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $last_error_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_error_code = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $consecutive_failures = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): static
    {
        $this->device = $device;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->last_seen_at;
    }

    public function setLastSeenAt(?\DateTimeImmutable $last_seen_at): static
    {
        $this->last_seen_at = $last_seen_at;

        return $this;
    }

    public function getLastValidEventAt(): ?\DateTimeImmutable
    {
        return $this->last_valid_event_at;
    }

    public function setLastValidEventAt(?\DateTimeImmutable $last_valid_event_at): static
    {
        $this->last_valid_event_at = $last_valid_event_at;

        return $this;
    }

    public function getLastProcessedAt(): ?\DateTimeImmutable
    {
        return $this->last_processed_at;
    }

    public function setLastProcessedAt(?\DateTimeImmutable $last_processed_at): static
    {
        $this->last_processed_at = $last_processed_at;

        return $this;
    }

    public function getLastErrorAt(): ?\DateTimeImmutable
    {
        return $this->last_error_at;
    }

    public function setLastErrorAt(?\DateTimeImmutable $last_error_at): static
    {
        $this->last_error_at = $last_error_at;

        return $this;
    }

    public function getLastErrorCode(): ?string
    {
        return $this->last_error_code;
    }

    public function setLastErrorCode(?string $last_error_code): static
    {
        $this->last_error_code = $last_error_code;

        return $this;
    }

    public function getConsecutiveFailures(): int
    {
        return $this->consecutive_failures;
    }

    public function setConsecutiveFailures(int $consecutive_failures): static
    {
        $this->consecutive_failures = $consecutive_failures;

        return $this;
    }

    public function incrementConsecutiveFailures(): static
    {
        ++$this->consecutive_failures;

        return $this;
    }

    public function resetConsecutiveFailures(): static
    {
        $this->consecutive_failures = 0;

        return $this;
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
