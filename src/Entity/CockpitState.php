<?php

namespace App\Entity;

use App\Repository\CockpitStateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CockpitStateRepository::class)]
#[ORM\Table(name: 'cockpit_state')]
class CockpitState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Cockpit::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Cockpit $cockpit = null;

    #[ORM\Column(type: Types::BIGINT)]
    private string $total_requested = '0'; // String for BIGINT

    #[ORM\Column(type: Types::BIGINT)]
    private string $total_produced = '0'; // String for BIGINT

    #[ORM\Column(type: Types::BIGINT)]
    private string $current_balance = '0'; // String for BIGINT

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCockpit(): ?Cockpit
    {
        return $this->cockpit;
    }

    public function setCockpit(Cockpit $cockpit): static
    {
        $this->cockpit = $cockpit;
        return $this;
    }

    public function getTotalRequested(): string
    {
        return $this->total_requested;
    }

    public function setTotalRequested(string $total_requested): static
    {
        $this->total_requested = $total_requested;
        return $this;
    }

    public function getTotalProduced(): string
    {
        return $this->total_produced;
    }

    public function setTotalProduced(string $total_produced): static
    {
        $this->total_produced = $total_produced;
        return $this;
    }

    public function getCurrentBalance(): string
    {
        return $this->current_balance;
    }

    public function setCurrentBalance(string $current_balance): static
    {
        $this->current_balance = $current_balance;
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

    public function getVersion(): int
    {
        return $this->version;
    }
}
