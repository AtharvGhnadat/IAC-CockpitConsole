<?php

declare(strict_types=1);

namespace App\Application\DTO;

class DeviceEventEnvelope
{
    private string $sourceType;
    private array $rawPayload;
    private ?string $sourceIp;
    private ?\DateTimeImmutable $deviceTimestamp;

    public function __construct(
        string $sourceType,
        array $rawPayload,
        ?string $sourceIp = null,
        ?\DateTimeImmutable $deviceTimestamp = null,
    ) {
        $this->sourceType = $sourceType;
        $this->rawPayload = $rawPayload;
        $this->sourceIp = $sourceIp;
        $this->deviceTimestamp = $deviceTimestamp;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function getRawPayload(): array
    {
        return $this->rawPayload;
    }

    public function getSourceIp(): ?string
    {
        return $this->sourceIp;
    }

    public function getDeviceTimestamp(): ?\DateTimeImmutable
    {
        return $this->deviceTimestamp;
    }
}
