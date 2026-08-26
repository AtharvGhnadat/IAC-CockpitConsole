<?php

namespace App\Repository;

use App\Entity\DeviceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeviceEvent>
 */
class DeviceEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceEvent::class);
    }

    /**
     * @return DeviceEvent[] Returns an array of unprocessed DeviceEvent objects
     */
    public function findUnprocessedEvents(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.processing_status = :status')
            ->setParameter('status', 'received')
            ->orderBy('d.received_at', 'ASC')
            ->addOrderBy('d.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }
}
