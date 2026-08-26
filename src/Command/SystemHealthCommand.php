<?php

namespace App\Command;

use App\Application\Service\SystemHealthService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:health',
    description: 'Displays the current operational health of CockpitConsole.'
)]
class SystemHealthCommand extends Command
{
    private SystemHealthService $healthService;

    public function __construct(SystemHealthService $healthService)
    {
        parent::__construct();
        $this->healthService = $healthService;
    }

    protected function configure(): void
    {
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Output health snapshot as JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $snapshot = $this->healthService->getHealthSnapshot();
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('json')) {
            $output->writeln(json_encode($snapshot, JSON_PRETTY_PRINT));
            return match($snapshot['overall']) {
                'CRITICAL' => 2,
                'WARNING' => 1,
                default => 0,
            };
        }

        // Pretty print for operators
        $io->title('CockpitConsole Operational Health');

        if ($snapshot['overall'] === 'HEALTHY') {
            $io->success('Overall System Status: ' . $snapshot['overall']);
        } elseif ($snapshot['overall'] === 'WARNING') {
            $io->warning('Overall System Status: ' . $snapshot['overall']);
        } else {
            $io->error('Overall System Status: ' . $snapshot['overall']);
        }

        $io->section('Infrastructure');
        $io->writeln('Database: ' . $this->formatStatus($snapshot['database']['status']));

        $io->section('Processing Backlog');
        $io->writeln('Status: ' . $this->formatStatus($snapshot['processing']['status']));
        $io->writeln('Pending Events: ' . $snapshot['processing']['pending_count']);
        if ($snapshot['processing']['pending_count'] > 0) {
            $io->writeln('Oldest Pending Age: ' . $snapshot['processing']['oldest_pending_seconds'] . ' seconds');
        }
        $io->writeln('Unresolved Failures: ' . $snapshot['processing']['unresolved_failures']);

        $io->section('Devices');
        if (empty($snapshot['devices'])) {
            $io->writeln('No devices found or active.');
        } else {
            foreach ($snapshot['devices'] as $code => $health) {
                $statusStr = $this->formatStatus($health['status']);
                if (isset($health['last_seen_seconds'])) {
                    $statusStr .= sprintf(' (Last seen: %d sec ago)', $health['last_seen_seconds']);
                }
                if (isset($health['failures'])) {
                    $statusStr .= sprintf(' (Consecutive Failures: %d)', $health['failures']);
                }
                $io->writeln(str_pad($code . ':', 15) . $statusStr);
            }
        }

        return match($snapshot['overall']) {
            'CRITICAL' => 2,
            'WARNING' => 1,
            default => 0,
        };
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'HEALTHY', 'ONLINE' => '<fg=green>' . $status . '</>',
            'WARNING', 'DELAYED', 'ERROR' => '<fg=yellow>' . $status . '</>',
            'CRITICAL', 'OFFLINE' => '<fg=red>' . $status . '</>',
            'DISABLED' => '<fg=gray>' . $status . '</>',
            default => '<fg=default>' . $status . '</>',
        };
    }
}
