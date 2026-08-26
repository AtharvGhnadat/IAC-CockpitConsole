<?php

namespace App\Repository;

use App\Entity\CockpitModelMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CockpitModelMapping>
 */
class CockpitModelMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CockpitModelMapping::class);
    }

    public function findMappingByScannerModel(string $scannerModel): ?CockpitModelMapping
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.scanner_model = :model')
            ->andWhere('m.is_active = :active')
            ->setParameter('model', $scannerModel)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
