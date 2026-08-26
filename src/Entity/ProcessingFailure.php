<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProcessingFailureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessingFailureRepository::class)]
#[ORM\Table(name: 'processing_failures')]
class ProcessingFailure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DeviceEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeviceEvent $device_event = null;

    #[ORM\Column(length: 100)]
    private ?string $failure_type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?int $attempt_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $exception_class = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $context = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolved_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resolution_note = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceEvent(): ?DeviceEvent
    {
        return $this->device_event;
    }

    public function setDeviceEvent(?DeviceEvent $device_event): static
    {
        $this->device_event = $device_event;

        return $this;
    }

    public function getFailureType(): ?string
    {
        return $this->failure_type;
    }

    public function setFailureType(string $failure_type): static
    {
        $this->failure_type = $failure_type;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getAttemptNumber(): ?int
    {
        return $this->attempt_number;
    }

    public function setAttemptNumber(int $attempt_number): static
    {
        $this->attempt_number = $attempt_number;

        return $this;
    }

    public function getExceptionClass(): ?string
    {
        return $this->exception_class;
    }

    public function setExceptionClass(?string $exception_class): static
    {
        $this->exception_class = $exception_class;

        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolved_at;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolved_at): static
    {
        $this->resolved_at = $resolved_at;

        return $this;
    }

    public function getResolutionNote(): ?string
    {
        return $this->resolution_note;
    }

    public function setResolutionNote(?string $resolution_note): static
    {
        $this->resolution_note = $resolution_note;

        return $this;
    }
}
