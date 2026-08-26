<?php

namespace App\Command;

use App\Application\Processing\Scanner1ProductionProcessor;
use App\Repository\DeviceEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:process-pending-production',
    description: 'Safely process or retry pending Scanner1 events.',
)]
class ProcessPendingProductionCommand extends Command
{
    private DeviceEventRepository $deviceEventRepo;
    private Scanner1ProductionProcessor $scanner1Processor;

    public function __construct(
        DeviceEventRepository $deviceEventRepo,
        Scanner1ProductionProcessor $scanner1Processor
    ) {
        parent::__construct();
        $this->deviceEventRepo = $deviceEventRepo;
        $this->scanner1Processor = $scanner1Processor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Scanner1 Pending Event Processor');

        // Find all scanner1 events that are 'received' or 'failed'
        $pendingEvents = $this->deviceEventRepo->createQueryBuilder('e')
            ->where('e.source_type = :sourceType')
            ->andWhere('e.processing_status IN (:statuses)')
            ->setParameter('sourceType', 'scanner1')
            ->setParameter('statuses', ['received', 'failed'])
            ->orderBy('e.received_at', 'ASC')
            ->getQuery()
            ->getResult();

        $count = count($pendingEvents);
        if ($count === 0) {
            $io->success('No pending scanner1 events found.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d pending scanner1 events. Processing...', $count));

        $success = 0;
        $failed = 0;

        foreach ($pendingEvents as $event) {
            try {
                $this->scanner1Processor->process($event);
                if ($event->getProcessingStatus() === 'processed') {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                $io->error(sprintf('Event ID %s failed: %s', $event->getId(), $e->getMessage()));
            }
        }

        $io->success(sprintf('Completed. Success: %d, Failed/Skipped: %d', $success, $failed));

        return Command::SUCCESS;
    }
}
