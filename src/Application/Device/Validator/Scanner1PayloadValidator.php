<?php

namespace App\Application\Device\Validator;

class Scanner1PayloadValidator implements DevicePayloadValidatorInterface
{
    public function validateStructure(array $payload): void
    {
        $requiredFields = ['scanner', 'model', 'quantity', 'scandatetime'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new \InvalidArgumentException(sprintf('Missing required field: %s', $field));
            }
        }

        if ($payload['scanner'] !== 'scanner1') {
            throw new \InvalidArgumentException('Expected scanner1 identifier in payload.');
        }

        // Validate quantity structure (string representing a non-negative integer)
        $quantity = $payload['quantity'];
        if (!is_string($quantity) || !ctype_digit($quantity)) {
            throw new \InvalidArgumentException('Quantity must be a valid non-negative integer string.');
        }
    }

    public function extractTimestamp(array $payload): \DateTimeImmutable
    {
        $timeString = $payload['scandatetime'];
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timeString);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $timeString) {
            throw new \InvalidArgumentException(sprintf('Invalid scandatetime format: %s', $timeString));
        }
        return $dt;
    }

    public function extractDeviceIdentifier(array $payload): ?string
    {
        // For scanners, we usually use the model as a lookup parameter to find the cockpit,
        // but the physical device itself might be registered as "scanner1".
        // The project instructions state: "Try to resolve the device using appropriate information such as: Scanner: scanner".
        return $payload['scanner'] ?? null;
    }
}
