<?php

namespace App\Command;

use App\Application\Processing\PlcRequestProcessor;
use App\Repository\DeviceEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:process-pending-plc',
    description: 'Safely process or retry pending PLC events.',
)]
class ProcessPendingPlcCommand extends Command
{
    private DeviceEventRepository $deviceEventRepo;
    private PlcRequestProcessor $plcProcessor;

    public function __construct(
        DeviceEventRepository $deviceEventRepo,
        PlcRequestProcessor $plcProcessor
    ) {
        parent::__construct();
        $this->deviceEventRepo = $deviceEventRepo;
        $this->plcProcessor = $plcProcessor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('PLC Pending Event Processor');

        // Find all PLC events that are 'received' or 'failed'
        $pendingEvents = $this->deviceEventRepo->createQueryBuilder('e')
            ->where('e.source_type = :sourceType')
            ->andWhere('e.processing_status IN (:statuses)')
            ->setParameter('sourceType', 'plc')
            ->setParameter('statuses', ['received', 'failed'])
            ->orderBy('e.received_at', 'ASC')
            ->getQuery()
            ->getResult();

        $count = count($pendingEvents);
        if ($count === 0) {
            $io->success('No pending PLC events found.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d pending PLC events. Processing...', $count));

        $success = 0;
        $failed = 0;

        foreach ($pendingEvents as $event) {
            try {
                $this->plcProcessor->process($event);
                // Check if it's marked processed
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
