<?php

namespace App\Repository;

use App\Entity\FingerprintUserMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FingerprintUserMapping>
 */
class FingerprintUserMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FingerprintUserMapping::class);
    }

    public function findActiveMapping(string $esslUsername, string $machineIp): ?FingerprintUserMapping
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.essl_username = :username')
            ->andWhere('m.machine_ip = :ip')
            ->andWhere('m.is_active = :active')
            ->setParameter('username', $esslUsername)
            ->setParameter('ip', $machineIp)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
