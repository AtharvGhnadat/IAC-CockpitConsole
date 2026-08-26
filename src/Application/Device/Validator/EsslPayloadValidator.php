<?php

declare(strict_types=1);

namespace App\Application\Device\Validator;

class EsslPayloadValidator implements DevicePayloadValidatorInterface
{
    public function validateStructure(array $payload): void
    {
        $requiredFields = ['machine_ip', 'user_name', 'privilege', 'punch_time'];
        foreach ($requiredFields as $field) {
            if (!\array_key_exists($field, $payload)) {
                throw new \InvalidArgumentException(\sprintf('Missing required field: %s', $field));
            }
        }
    }

    public function extractTimestamp(array $payload): \DateTimeImmutable
    {
        $timeString = $payload['punch_time'];
        // Expected format: Y-m-d H:i:s
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timeString);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $timeString) {
            throw new \InvalidArgumentException(\sprintf('Invalid punch_time format: %s', $timeString));
        }

        return $dt;
    }

    public function extractDeviceIdentifier(array $payload): ?string
    {
        return $payload['machine_ip'] ?? null;
    }
}
