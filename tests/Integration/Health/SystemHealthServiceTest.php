<?php

namespace App\Tests\Integration\Health;

use App\Application\Service\SystemHealthService;
use App\Entity\Device;
use App\Entity\DeviceHealth;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SystemHealthServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private SystemHealthService $healthService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $this->em = $container->get('doctrine')->getManager();
        $this->healthService = $container->get(SystemHealthService::class);
        
        $this->em->getConnection()->executeStatement('DELETE FROM device_events');
        $this->em->getConnection()->executeStatement('DELETE FROM device_health');
        $this->em->getConnection()->executeStatement('DELETE FROM devices');
    }

    public function testOverallHealthyState()
    {
        // Add a healthy device
        $device = new Device();
        $device->setDeviceCode('PLC-01');
        $device->setDeviceType('plc');
        $device->setIsActive(true);
        $this->em->persist($device);
        
        $health = new DeviceHealth();
        $health->setDevice($device);
        $health->setLastSeenAt(new \DateTimeImmutable());
        $health->setLastValidEventAt(new \DateTimeImmutable());
        $health->setLastProcessedAt(new \DateTimeImmutable());
        $this->em->persist($health);
        
        $this->em->flush();
        
        $snapshot = $this->healthService->getHealthSnapshot();
        
        $this->assertEquals('HEALTHY', $snapshot['overall']);
        $this->assertEquals('HEALTHY', $snapshot['database']['status']);
        $this->assertEquals('HEALTHY', $snapshot['processing']['status']);
        $this->assertArrayHasKey('PLC-01', $snapshot['devices']);
        $this->assertEquals('ONLINE', $snapshot['devices']['PLC-01']['status']);
    }

    public function testDelayedDeviceCausesWarning()
    {
        $device = new Device();
        $device->setDeviceCode('PLC-01');
        $device->setDeviceType('plc');
        $device->setIsActive(true);
        $this->em->persist($device);
        
        $health = new DeviceHealth();
        $health->setDevice($device);
        $health->setLastSeenAt(new \DateTimeImmutable('-3 minutes')); // Beyond 120s delay threshold
        $this->em->persist($health);
        
        $this->em->flush();
        
        $snapshot = $this->healthService->getHealthSnapshot();
        
        $this->assertEquals('WARNING', $snapshot['overall']);
        $this->assertEquals('DELAYED', $snapshot['devices']['PLC-01']['status']);
    }
    
    public function testConsecutiveFailuresCausesWarning()
    {
        $device = new Device();
        $device->setDeviceCode('PLC-01');
        $device->setDeviceType('plc');
        $device->setIsActive(true);
        $this->em->persist($device);
        
        $health = new DeviceHealth();
        $health->setDevice($device);
        $health->setLastSeenAt(new \DateTimeImmutable());
        $health->setConsecutiveFailures(3);
        $this->em->persist($health);
        
        $this->em->flush();
        
        $snapshot = $this->healthService->getHealthSnapshot();
        
        $this->assertEquals('WARNING', $snapshot['overall']);
        $this->assertEquals('ERROR', $snapshot['devices']['PLC-01']['status']);
    }
}
