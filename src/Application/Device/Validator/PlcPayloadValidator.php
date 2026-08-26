<?php

namespace App\Application\Device\Validator;

class PlcPayloadValidator implements DevicePayloadValidatorInterface
{
    public function validateStructure(array $payload): void
    {
        $requiredFields = ['cockpit', 'dateTime'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new \InvalidArgumentException(sprintf('Missing required field: %s', $field));
            }
        }
        
        if (empty($payload['cockpit'])) {
            throw new \InvalidArgumentException('Cockpit code cannot be empty.');
        }
    }

    public function extractTimestamp(array $payload): \DateTimeImmutable
    {
        $timeString = $payload['dateTime'];
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $timeString);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $timeString) {
            throw new \InvalidArgumentException(sprintf('Invalid dateTime format: %s', $timeString));
        }
        return $dt;
    }

    public function extractDeviceIdentifier(array $payload): ?string
    {
        // For PLC, we identify the physical device by the "cockpit" code sent in the request
        return $payload['cockpit'] ?? null;
    }
}
