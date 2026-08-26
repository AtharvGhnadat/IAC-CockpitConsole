<?php

namespace App\Entity;

use App\Repository\FingerprintUserMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FingerprintUserMappingRepository::class)]
#[ORM\Table(name: 'fingerprint_user_mappings')]
#[ORM\Index(name: 'idx_essl_machine', columns: ['essl_username', 'machine_ip'])]
class FingerprintUserMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $essl_username = null;

    #[ORM\Column(length: 45)]
    private ?string $machine_ip = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEsslUsername(): ?string
    {
        return $this->essl_username;
    }

    public function setEsslUsername(string $essl_username): static
    {
        $this->essl_username = $essl_username;
        return $this;
    }

    public function getMachineIp(): ?string
    {
        return $this->machine_ip;
    }

    public function setMachineIp(string $machine_ip): static
    {
        $this->machine_ip = $machine_ip;
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
