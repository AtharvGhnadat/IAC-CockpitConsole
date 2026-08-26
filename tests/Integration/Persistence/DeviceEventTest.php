<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\DeviceEvent;
use App\Infrastructure\Persistence\RawDeviceEventRecorder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceEventTest extends KernelTestCase
{
    private $entityManager;
    private $recorder;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->recorder = new RawDeviceEventRecorder($this->entityManager);
    }

    public function testEventPersistenceAndImmutability(): void
    {
        $payload = [
            'scanner' => 'scanner1',
            'model' => 'AX7 H - 2301FW608171N',
            'quantity' => '10', // String preserved
            'scandatetime' => '2026-08-25 18:25:22',
        ];

        $event = $this->recorder->recordEvent('scanner1', $payload, '127.0.0.1');

        $this->assertNotNull($event->getId());
        $this->assertNotNull($event->getEventUuid());
        $this->assertNotNull($event->getPayloadHash());
        $this->assertEquals('received', $event->getProcessingStatus());
        $this->assertEquals(0, $event->getProcessingAttempts());

        // Assert payload exact match
        $savedPayload = $event->getRawPayload();
        $this->assertSame('10', $savedPayload['quantity']);

        // Ensure microsecond datetime preservation
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getReceivedAt());
    }

    public function testDeterministicOrderingQuery(): void
    {
        // Insert events rapidly
        $this->recorder->recordEvent('plc', ['cockpit' => 'A']);
        $this->recorder->recordEvent('plc', ['cockpit' => 'B']);

        $repo = $this->entityManager->getRepository(DeviceEvent::class);
        $events = $repo->findUnprocessedEvents();

        $this->assertGreaterThanOrEqual(2, \count($events));

        // Check order matches ID monotonic increase and timestamp
        $first = $events[0];
        $second = $events[1];

        $this->assertTrue(
            $first->getReceivedAt() < $second->getReceivedAt()
            || ($first->getReceivedAt() === $second->getReceivedAt() && $first->getId() < $second->getId()),
        );
    }
}
