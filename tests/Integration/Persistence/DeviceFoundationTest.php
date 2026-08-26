<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\Cockpit;
use App\Entity\CockpitModelMapping;
use App\Entity\Device;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceFoundationTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testDevicePersistenceAndUniqueness(): void
    {
        $device1 = new Device();
        $device1->setDeviceCode('TEST-DEV-01');
        $device1->setDeviceType('scanner1');

        $this->entityManager->persist($device1);
        $this->entityManager->flush();

        $this->assertNotNull($device1->getId());

        $device2 = new Device();
        $device2->setDeviceCode('TEST-DEV-01'); // Duplicate code
        $device2->setDeviceType('scanner2');

        $this->entityManager->persist($device2);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testCockpitAndMappingPersistence(): void
    {
        $cockpit = new Cockpit();
        $cockpit->setCockpitCode('TEST-COCKPIT-01');

        $this->entityManager->persist($cockpit);

        $mapping = new CockpitModelMapping();
        $mapping->setCockpit($cockpit);
        $mapping->setScannerModel('TEST-MODEL-A');
        $mapping->setMappingType('direct');

        $this->entityManager->persist($mapping);
        $this->entityManager->flush();

        $this->assertNotNull($cockpit->getId());
        $this->assertNotNull($mapping->getId());
        $this->assertSame('TEST-COCKPIT-01', $mapping->getCockpit()->getCockpitCode());
    }
}
