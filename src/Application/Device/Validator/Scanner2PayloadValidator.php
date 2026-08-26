<?php

declare(strict_types=1);

namespace App\Application\Device\Validator;

class Scanner2PayloadValidator implements DevicePayloadValidatorInterface
{
    public function validateStructure(array $payload): void
    {
        $requiredFields = ['scanner', 'model', 'quantity', 'scandatetime'];
        foreach ($requiredFields as $field) {
            if (!\array_key_exists($field, $payload)) {
                throw new \InvalidArgumentException(\sprintf('Missing required field: %s', $field));
            }
        }

        if ($payload['scanner'] !== 'scanner2') {
            throw new \InvalidArgumentException('Expected scanner2 identifier in payload.');
        }

        // Validate quantity structure (string representing a non-negative integer)
        $quantity = $payload['quantity'];
        if (!\is_string($quantity) || !ctype_digit($quantity)) {
            throw new \InvalidArgumentException('Quantity must be a valid non-negative integer string.');
        }
    }

    public function extractTimestamp(array $payload): \DateTimeImmutable
    {
        $timeString = $payload['scandatetime'];
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timeString);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $timeString) {
            throw new \InvalidArgumentException(\sprintf('Invalid scandatetime format: %s', $timeString));
        }

        return $dt;
    }

    public function extractDeviceIdentifier(array $payload): ?string
    {
        return $payload['scanner'] ?? null;
    }
}
