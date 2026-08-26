<?php

declare(strict_types=1);

namespace App\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SystemHealthService
{
    private EntityManagerInterface $em;
    private int $delayWarningThreshold;
    private int $offlineThreshold;
    private int $backlogWarningThreshold;
    private int $backlogCriticalThreshold;
    private int $failedWarningCount;

    public function __construct(
        EntityManagerInterface $em,
        #[Autowire(env: 'int:DEVICE_HEALTH_DELAY_SECONDS')] int $delayWarningThreshold,
        #[Autowire(env: 'int:DEVICE_HEALTH_OFFLINE_SECONDS')] int $offlineThreshold,
        #[Autowire(env: 'int:PROCESSING_BACKLOG_WARNING_SECONDS')] int $backlogWarningThreshold,
        #[Autowire(env: 'int:PROCESSING_BACKLOG_CRITICAL_SECONDS')] int $backlogCriticalThreshold,
        #[Autowire(env: 'int:FAILED_EVENT_WARNING_COUNT')] int $failedWarningCount,
    ) {
        $this->em = $em;
        $this->delayWarningThreshold = $delayWarningThreshold;
        $this->offlineThreshold = $offlineThreshold;
        $this->backlogWarningThreshold = $backlogWarningThreshold;
        $this->backlogCriticalThreshold = $backlogCriticalThreshold;
        $this->failedWarningCount = $failedWarningCount;
    }

    public function getHealthSnapshot(): array
    {
        $snapshot = [
            'overall' => 'HEALTHY',
            'database' => ['status' => 'UNKNOWN'],
            'processing' => ['status' => 'UNKNOWN', 'oldest_pending_seconds' => 0, 'pending_count' => 0, 'unresolved_failures' => 0],
            'devices' => [],
            'invariants' => ['status' => 'HEALTHY'], // We'll mock lightweight integrity for now
        ];

        // 1. Database Health
        try {
            $this->em->getConnection()->executeQuery('SELECT 1');
            $snapshot['database']['status'] = 'HEALTHY';
        } catch (\Exception $e) {
            $snapshot['database']['status'] = 'CRITICAL';
            $snapshot['overall'] = 'CRITICAL';

            return $snapshot; // If DB is down, we cannot check the rest
        }

        // 2. Processing Backlog & Failures
        $conn = $this->em->getConnection();

        $pendingStats = $conn->fetchAssociative('
            SELECT COUNT(*) as cnt, MIN(received_at) as oldest 
            FROM device_events 
            WHERE processing_status = "received"
        ');

        $pendingCount = (int) ($pendingStats['cnt'] ?? 0);
        $snapshot['processing']['pending_count'] = $pendingCount;

        if ($pendingCount > 0 && isset($pendingStats['oldest'])) {
            $oldestTime = new \DateTimeImmutable($pendingStats['oldest']);
            $now = new \DateTimeImmutable();
            $ageSeconds = max(0, $now->getTimestamp() - $oldestTime->getTimestamp());
            $snapshot['processing']['oldest_pending_seconds'] = $ageSeconds;

            if ($ageSeconds >= $this->backlogCriticalThreshold) {
                $snapshot['processing']['status'] = 'CRITICAL';
                $snapshot['overall'] = 'CRITICAL';
            } elseif ($ageSeconds >= $this->backlogWarningThreshold) {
                $snapshot['processing']['status'] = 'WARNING';
                if ($snapshot['overall'] !== 'CRITICAL') {
                    $snapshot['overall'] = 'WARNING';
                }
            } else {
                $snapshot['processing']['status'] = 'HEALTHY';
            }
        } else {
            $snapshot['processing']['status'] = 'HEALTHY';
        }

        $failedCount = (int) $conn->fetchOne('SELECT COUNT(*) FROM device_events WHERE processing_status = "failed"');
        $snapshot['processing']['unresolved_failures'] = $failedCount;

        if ($failedCount >= $this->failedWarningCount) {
            if ($snapshot['processing']['status'] === 'HEALTHY') {
                $snapshot['processing']['status'] = 'WARNING';
            }
            if ($snapshot['overall'] !== 'CRITICAL') {
                $snapshot['overall'] = 'WARNING';
            }
        }

        // 3. Device Health
        $deviceHealths = $conn->fetchAllAssociative('
            SELECT d.device_code, d.is_active, dh.last_seen_at, dh.last_valid_event_at, dh.last_processed_at, dh.consecutive_failures
            FROM device_health dh
            JOIN devices d ON d.id = dh.device_id
        ');

        $now = time();
        foreach ($deviceHealths as $dh) {
            $code = $dh['device_code'];

            if (!$dh['is_active']) {
                $snapshot['devices'][$code] = ['status' => 'DISABLED'];

                continue;
            }

            if (!$dh['last_seen_at']) {
                $snapshot['devices'][$code] = ['status' => 'UNKNOWN'];

                continue;
            }

            if ($dh['consecutive_failures'] > 0) {
                $snapshot['devices'][$code] = ['status' => 'ERROR', 'failures' => $dh['consecutive_failures']];
                if ($snapshot['overall'] !== 'CRITICAL') {
                    $snapshot['overall'] = 'WARNING';
                }

                continue;
            }

            $lastSeenTime = strtotime($dh['last_seen_at']);
            $age = max(0, $now - $lastSeenTime);

            if ($age >= $this->offlineThreshold) {
                $snapshot['devices'][$code] = ['status' => 'OFFLINE', 'last_seen_seconds' => $age];
                if ($snapshot['overall'] !== 'CRITICAL') {
                    $snapshot['overall'] = 'WARNING';
                }
            } elseif ($age >= $this->delayWarningThreshold) {
                $snapshot['devices'][$code] = ['status' => 'DELAYED', 'last_seen_seconds' => $age];
                if ($snapshot['overall'] !== 'CRITICAL') {
                    $snapshot['overall'] = 'WARNING';
                }
            } else {
                $snapshot['devices'][$code] = ['status' => 'ONLINE', 'last_seen_seconds' => $age];
            }
        }

        return $snapshot;
    }
}
