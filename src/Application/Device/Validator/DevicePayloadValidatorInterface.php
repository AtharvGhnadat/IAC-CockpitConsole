<?php

declare(strict_types=1);

namespace App\Application\Device\Validator;

interface DevicePayloadValidatorInterface
{
    /**
     * Checks if the payload contains the required fields for this specific source.
     * Throws an exception if validation fails.
     *
     * @param array $payload the raw JSON decoded to an array
     *
     * @throws \InvalidArgumentException if structurally invalid
     */
    public function validateStructure(array $payload): void;

    /**
     * Extracts and normalizes the device timestamp from the raw payload.
     *
     * @param array $payload The raw payload
     *
     * @throws \InvalidArgumentException If the timestamp cannot be parsed
     */
    public function extractTimestamp(array $payload): \DateTimeImmutable;

    /**
     * Optional method to extract the device identifier (e.g. machine_ip, cockpit code, scanner model)
     * so that it can be looked up in the database.
     */
    public function extractDeviceIdentifier(array $payload): ?string;
}
