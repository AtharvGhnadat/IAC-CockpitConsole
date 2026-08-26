<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Application\Service\DeviceIngestionService;
use App\Entity\DeviceEvent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceIngestionServiceTest extends KernelTestCase
{
    private DeviceIngestionService $ingestionService;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->ingestionService = $kernel->getContainer()->get(DeviceIngestionService::class);
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testIngestEsslPayload(): void
    {
        $json = json_encode([
            'machine_ip' => '192.168.1.205',
            'user_name' => 'Sachinadmin',
            'privilege' => 'User',
            'punch_time' => '2026-08-25 18:25:22',
        ]);

        $event = $this->ingestionService->ingest('essl', $json, '127.0.0.1');

        $this->assertInstanceOf(DeviceEvent::class, $event);
        $this->assertSame('essl', $event->getSourceType());
        $this->assertSame('127.0.0.1', $event->getSourceIp());
        $this->assertNotNull($event->getDeviceTimestamp());
        $this->assertEquals('2026-08-25 18:25:22', $event->getDeviceTimestamp()->format('Y-m-d H:i:s'));
        $this->assertEquals('received', $event->getProcessingStatus());
    }

    public function testIngestPlcPayload(): void
    {
        $json = json_encode([
            'cockpit' => '2301AZ106071N',
            'dateTime' => '2026-08-25 18:25:22',
        ]);

        $event = $this->ingestionService->ingest('plc', $json, '192.168.1.50');

        $this->assertInstanceOf(DeviceEvent::class, $event);
        $this->assertSame('plc', $event->getSourceType());
    }

    public function testIngestScanner1Payload(): void
    {
        $json = json_encode([
            'scanner' => 'scanner1',
            'model' => 'AX7 H - 2301FW608171N',
            'quantity' => '10',
            'scandatetime' => '2026-08-25 18:25:22',
        ]);

        $event = $this->ingestionService->ingest('scanner1', $json, '192.168.1.60');

        $this->assertInstanceOf(DeviceEvent::class, $event);
        $this->assertSame('scanner1', $event->getSourceType());
        // Verify string quantity preservation
        $this->assertSame('10', $event->getRawPayload()['quantity']);
    }

    public function testMalformedJsonRejection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ingestionService->ingest('plc', '{malformed:', '127.0.0.1');
    }

    public function testMissingRequiredFieldRejection(): void
    {
        $json = json_encode([
            'cockpit' => '2301AZ106071N',
            // Missing dateTime
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->ingestionService->ingest('plc', $json, '127.0.0.1');
    }

    public function testInvalidQuantityFormatRejection(): void
    {
        $json = json_encode([
            'scanner' => 'scanner1',
            'model' => 'AX7 H - 2301FW608171N',
            'quantity' => '10.5', // Decimal not allowed
            'scandatetime' => '2026-08-25 18:25:22',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->ingestionService->ingest('scanner1', $json, '127.0.0.1');
    }
}
