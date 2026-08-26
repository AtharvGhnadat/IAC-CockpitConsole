<?php

namespace App\Repository;

use App\Entity\ProductionQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductionQueue>
 *
 * @method ProductionQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductionQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductionQueue[]    findAll()
 * @method ProductionQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionQueue::class);
    }
}
