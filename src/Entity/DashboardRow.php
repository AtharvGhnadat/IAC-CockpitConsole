<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DashboardRowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DashboardRowRepository::class)]
#[ORM\Table(name: 'dashboard_rows')]
class DashboardRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private int $display_order = 0;

    #[ORM\Column]
    private bool $is_visible = true;

    /**
     * @var Collection<int, DashboardColumn>
     */
    #[ORM\OneToMany(targetEntity: DashboardColumn::class, mappedBy: 'dashboardRow', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['display_order' => 'ASC'])]
    private Collection $dashboardColumns;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->dashboardColumns = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, DashboardColumn>
     */
    public function getDashboardColumns(): Collection
    {
        return $this->dashboardColumns;
    }

    public function addDashboardColumn(DashboardColumn $dashboardColumn): static
    {
        if (!$this->dashboardColumns->contains($dashboardColumn)) {
            $this->dashboardColumns->add($dashboardColumn);
            $dashboardColumn->setDashboardRow($this);
        }

        return $this;
    }

    public function removeDashboardColumn(DashboardColumn $dashboardColumn): static
    {
        if ($this->dashboardColumns->removeElement($dashboardColumn)) {
            // set the owning side to null (unless already changed)
            if ($dashboardColumn->getDashboardRow() === $this) {
                $dashboardColumn->setDashboardRow(null);
            }
        }

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
