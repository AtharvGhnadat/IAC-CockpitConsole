<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RequestEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestEvent>
 *
 * @method RequestEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestEvent[] findAll()
 * @method RequestEvent[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestEvent::class);
    }
}
