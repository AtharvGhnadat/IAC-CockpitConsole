<?php

namespace App\Repository;

use App\Entity\ProductionEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductionEvent>
 *
 * @method ProductionEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductionEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductionEvent[]    findAll()
 * @method ProductionEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionEvent::class);
    }
}
