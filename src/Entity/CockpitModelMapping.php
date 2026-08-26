<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CockpitModelMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CockpitModelMappingRepository::class)]
#[ORM\Table(name: 'cockpit_model_mappings')]
#[ORM\Index(name: 'idx_scanner_model', columns: ['scanner_model'])]
class CockpitModelMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cockpit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cockpit $cockpit = null;

    #[ORM\Column(length: 255)]
    private ?string $scanner_model = null;

    #[ORM\Column(length: 50)]
    private ?string $mapping_type = null;

    #[ORM\Column]
    private bool $is_active = true;

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

    public function getMappingType(): ?string
    {
        return $this->mapping_type;
    }

    public function setMappingType(string $mapping_type): static
    {
        $this->mapping_type = $mapping_type;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

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
