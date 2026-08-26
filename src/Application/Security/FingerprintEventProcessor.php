<?php

declare(strict_types=1);

namespace App\Application\Security;

use App\Entity\AuditEvent;
use App\Entity\DeviceEvent;
use App\Entity\TerminalSession;
use App\Repository\FingerprintUserMappingRepository;
use App\Repository\TerminalRepository;
use App\Repository\TerminalSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class FingerprintEventProcessor
{
    private EntityManagerInterface $em;
    private FingerprintUserMappingRepository $mappingRepo;
    private TerminalRepository $terminalRepo;
    private TerminalSessionRepository $sessionRepo;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        FingerprintUserMappingRepository $mappingRepo,
        TerminalRepository $terminalRepo,
        TerminalSessionRepository $sessionRepo,
        LoggerInterface $deviceIngestionLogger, // using the specific channel
    ) {
        $this->em = $em;
        $this->mappingRepo = $mappingRepo;
        $this->terminalRepo = $terminalRepo;
        $this->sessionRepo = $sessionRepo;
        $this->logger = $deviceIngestionLogger;
    }

    public function process(DeviceEvent $event): void
    {
        if ($event->getSourceType() !== 'essl') {
            return;
        }

        // 1. Idempotency Check
        $existingSession = $this->sessionRepo->findActiveSessionByEventId((int) $event->getId());
        if ($existingSession) {
            $this->logger->info('Event already processed into a session, skipping.', [
                'event_id' => $event->getId(),
            ]);

            return;
        }

        $payload = $event->getRawPayload();
        $esslUsername = $payload['user_name'] ?? null;
        $machineIp = $payload['machine_ip'] ?? null;

        if (!$esslUsername || !$machineIp) {
            $this->logAndAuditRejected($event, 'Missing username or machine_ip in payload.');

            return;
        }

        $this->em->beginTransaction();

        try {
            // 2. Resolve User Mapping
            $mapping = $this->mappingRepo->findActiveMapping($esslUsername, $machineIp);
            if (!$mapping) {
                $this->logAndAuditRejected($event, 'Unknown fingerprint user or mapping inactive.');
                $this->em->commit();

                return;
            }

            $user = $mapping->getUser();
            if (!$user->isActive()) {
                $this->logAndAuditRejected($event, 'Mapped user is inactive.');
                $this->em->commit();

                return;
            }

            // 3. Resolve Terminal
            $terminal = $this->terminalRepo->findOneBy([
                'fingerprint_device_ip' => $machineIp,
                'is_active' => true,
            ]);

            if (!$terminal) {
                $this->logAndAuditRejected($event, 'No active terminal mapped for this fingerprint device.');
                $this->em->commit();

                return;
            }

            // 4. Handle Existing Session (Row locking for concurrency ideally, but we rely on transactions)
            $existingActiveSession = $this->sessionRepo->findActiveSessionForTerminal($terminal->getId());
            if ($existingActiveSession) {
                $existingActiveSession->setStatus('replaced');
                $existingActiveSession->setEndReason('replaced');
                $existingActiveSession->setEndedAt(new \DateTimeImmutable());
                $this->em->persist($existingActiveSession);

                $this->createAuditEvent('SESSION_REPLACED', 'Previous session replaced by new fingerprint scan.', [
                    'replaced_session_uuid' => $existingActiveSession->getSessionUuid(),
                    'terminal' => $terminal->getTerminalCode(),
                ], $user);
            }

            // 5. Create New Session
            $now = new \DateTimeImmutable();
            $newSession = new TerminalSession();
            $newSession->setSessionUuid(Uuid::v4()->toRfc4122());
            $newSession->setTerminal($terminal);
            $newSession->setUser($user);
            $newSession->setFingerprintEvent($event);
            $newSession->setRole($user->getRole());
            $newSession->setStartedAt($now);
            $newSession->setExpiresAt($now->modify('+1 hour'));
            $newSession->setStatus('active');

            $this->em->persist($newSession);

            $this->createAuditEvent('SESSION_STARTED', 'Dashboard session started via fingerprint.', [
                'session_uuid' => $newSession->getSessionUuid(),
                'terminal' => $terminal->getTerminalCode(),
                'user' => $user->getUsername(),
            ], $user);

            $this->logger->info('Successfully created terminal session for fingerprint event.', [
                'event_id' => $event->getId(),
                'session_uuid' => $newSession->getSessionUuid(),
                'terminal' => $terminal->getTerminalCode(),
            ]);

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Transaction failed during fingerprint processing.', [
                'error' => $e->getMessage(),
                'event_id' => $event->getId(),
            ]);

            throw $e;
        }
    }

    private function logAndAuditRejected(DeviceEvent $event, string $reason): void
    {
        $this->logger->warning('Fingerprint rejected: ' . $reason, [
            'event_id' => $event->getId(),
            'payload' => $event->getRawPayload(),
        ]);

        $audit = new AuditEvent();
        $audit->setEventType('FINGERPRINT_REJECTED');
        $audit->setDescription($reason);
        $audit->setContext([
            'event_uuid' => $event->getEventUuid(),
            'event_id' => $event->getId(),
            'source_ip' => $event->getSourceIp(),
        ]);

        $this->em->persist($audit);
        $this->em->flush();
    }

    private function createAuditEvent(string $action, string $description, array $context, ?\App\Entity\User $user = null): void
    {
        $audit = new AuditEvent();
        $audit->setEventType($action);
        $audit->setDescription($description);
        $audit->setContext($context);

        // Normally we might store the username in the description or context

        $this->em->persist($audit);
    }
}
