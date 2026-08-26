<?php

namespace App\Entity;

use App\Repository\AuditEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditEventRepository::class)]
#[ORM\Table(name: 'audit_events')]
#[ORM\Index(name: 'idx_event_type', columns: ['event_type'])]
class AuditEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $event_type = null;

    #[ORM\Column(length: 100)]
    private ?string $actor_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actor_identifier = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $entity_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entity_identifier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $context = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventType(): ?string
    {
        return $this->event_type;
    }

    public function setEventType(string $event_type): static
    {
        $this->event_type = $event_type;
        return $this;
    }

    public function getActorType(): ?string
    {
        return $this->actor_type;
    }

    public function setActorType(string $actor_type): static
    {
        $this->actor_type = $actor_type;
        return $this;
    }

    public function getActorIdentifier(): ?string
    {
        return $this->actor_identifier;
    }

    public function setActorIdentifier(?string $actor_identifier): static
    {
        $this->actor_identifier = $actor_identifier;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entity_type;
    }

    public function setEntityType(?string $entity_type): static
    {
        $this->entity_type = $entity_type;
        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->entity_identifier;
    }

    public function setEntityIdentifier(?string $entity_identifier): static
    {
        $this->entity_identifier = $entity_identifier;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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
}
