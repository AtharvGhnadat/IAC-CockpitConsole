<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DispatchEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DispatchEvent>
 *
 * @method DispatchEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method DispatchEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method DispatchEvent[] findAll()
 * @method DispatchEvent[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DispatchEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DispatchEvent::class);
    }
}
