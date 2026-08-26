<?php

namespace App\Entity;

use App\Repository\TerminalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerminalRepository::class)]
#[ORM\Table(name: 'terminals')]
class Terminal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $terminal_code = null;

    #[ORM\Column(length: 255)]
    private ?string $terminal_name = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $fingerprint_device_ip = null;

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

    public function getTerminalCode(): ?string
    {
        return $this->terminal_code;
    }

    public function setTerminalCode(string $terminal_code): static
    {
        $this->terminal_code = $terminal_code;
        return $this;
    }

    public function getTerminalName(): ?string
    {
        return $this->terminal_name;
    }

    public function setTerminalName(string $terminal_name): static
    {
        $this->terminal_name = $terminal_name;
        return $this;
    }

    public function getFingerprintDeviceIp(): ?string
    {
        return $this->fingerprint_device_ip;
    }

    public function setFingerprintDeviceIp(?string $fingerprint_device_ip): static
    {
        $this->fingerprint_device_ip = $fingerprint_device_ip;
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
