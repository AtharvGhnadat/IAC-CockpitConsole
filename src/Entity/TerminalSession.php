<?php

namespace App\Entity;

use App\Repository\TerminalSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerminalSessionRepository::class)]
#[ORM\Table(name: 'terminal_sessions')]
class TerminalSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $session_uuid = null;

    #[ORM\ManyToOne(targetEntity: Terminal::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Terminal $terminal = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: DeviceEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeviceEvent $fingerprint_event = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $started_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expires_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ended_at = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null; // 'active', 'expired', 'locked', 'replaced', 'logged_out'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $end_reason = null;

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

    public function getSessionUuid(): ?string
    {
        return $this->session_uuid;
    }

    public function setSessionUuid(string $session_uuid): static
    {
        $this->session_uuid = $session_uuid;
        return $this;
    }

    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    public function setTerminal(?Terminal $terminal): static
    {
        $this->terminal = $terminal;
        return $this;
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

    public function getFingerprintEvent(): ?DeviceEvent
    {
        return $this->fingerprint_event;
    }

    public function setFingerprintEvent(?DeviceEvent $fingerprint_event): static
    {
        $this->fingerprint_event = $fingerprint_event;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    public function setStartedAt(\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expires_at;
    }

    public function setExpiresAt(\DateTimeImmutable $expires_at): static
    {
        $this->expires_at = $expires_at;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->ended_at;
    }

    public function setEndedAt(?\DateTimeImmutable $ended_at): static
    {
        $this->ended_at = $ended_at;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getEndReason(): ?string
    {
        return $this->end_reason;
    }

    public function setEndReason(?string $end_reason): static
    {
        $this->end_reason = $end_reason;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
}
