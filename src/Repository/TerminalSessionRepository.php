<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TerminalSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TerminalSession>
 */
class TerminalSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TerminalSession::class);
    }

    public function findActiveSessionForTerminal(int $terminalId): ?TerminalSession
    {
        return $this->createQueryBuilder('ts')
            ->andWhere('ts.terminal = :terminal_id')
            ->andWhere('ts.status = :status')
            ->andWhere('ts.expires_at > :now')
            ->setParameter('terminal_id', $terminalId)
            ->setParameter('status', 'active')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('ts.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveSessionByEventId(int $eventId): ?TerminalSession
    {
        return $this->createQueryBuilder('ts')
            ->andWhere('ts.fingerprint_event = :event_id')
            ->setParameter('event_id', $eventId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
