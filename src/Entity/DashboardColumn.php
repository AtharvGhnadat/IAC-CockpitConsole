<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DashboardColumnRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DashboardColumnRepository::class)]
#[ORM\Table(name: 'dashboard_columns')]
class DashboardColumn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dashboardColumns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DashboardRow $dashboardRow = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $metric_key = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Cockpit $cockpit = null;

    #[ORM\Column]
    private int $display_order = 0;

    #[ORM\Column]
    private bool $is_visible = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDashboardRow(): ?DashboardRow
    {
        return $this->dashboardRow;
    }

    public function setDashboardRow(?DashboardRow $dashboardRow): static
    {
        $this->dashboardRow = $dashboardRow;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMetricKey(): ?string
    {
        return $this->metric_key;
    }

    public function setMetricKey(string $metric_key): static
    {
        $this->metric_key = $metric_key;

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

    public function getDisplayOrder(): int
    {
        return $this->display_order;
    }

    public function setDisplayOrder(int $display_order): static
    {
        $this->display_order = $display_order;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    public function setIsVisible(bool $is_visible): static
    {
        $this->is_visible = $is_visible;

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
