<?php

namespace App\Repository;

use App\Entity\Cockpit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cockpit>
 */
class CockpitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cockpit::class);
    }

    public function findCockpitByCode(string $code): ?Cockpit
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cockpit_code = :code')
            ->andWhere('c.is_active = :active')
            ->setParameter('code', $code)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
