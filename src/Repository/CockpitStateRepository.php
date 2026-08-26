<?php

namespace App\Repository;

use App\Entity\CockpitState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CockpitState>
 *
 * @method CockpitState|null find($id, $lockMode = null, $lockVersion = null)
 * @method CockpitState|null findOneBy(array $criteria, array $orderBy = null)
 * @method CockpitState[]    findAll()
 * @method CockpitState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CockpitStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CockpitState::class);
    }
}
