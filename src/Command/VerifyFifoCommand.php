<?php

namespace App\Command;

use App\Repository\CockpitStateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:verify-fifo',
    description: 'Verify the integrity of the Production FIFO Queue.',
)]
class VerifyFifoCommand extends Command
{
    private EntityManagerInterface $em;
    private CockpitStateRepository $stateRepo;

    public function __construct(EntityManagerInterface $em, CockpitStateRepository $stateRepo)
    {
        parent::__construct();
        $this->em = $em;
        $this->stateRepo = $stateRepo;
    }

    protected function configure(): void
    {
        $this
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Apply fixes (not implemented yet, reporting only)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('FIFO Queue Integrity Verification');

        $discrepancies = 0;

        // 1. Check for multiple active cockpits in production
        $inProductionCount = (int) $this->em->getConnection()->fetchOne(
            "SELECT COUNT(id) FROM production_queue WHERE status = 'in_production'"
        );
        if ($inProductionCount > 1) {
            $io->error(sprintf('CRITICAL: %d cockpits are marked as in_production!', $inProductionCount));
            $discrepancies++;
        } elseif ($inProductionCount === 1) {
            $io->info('1 cockpit is correctly in production.');
        } else {
            $io->info('No cockpits currently in production.');
        }

        // 2. Check for cockpits with positive balance but NO active queue entry
        $states = $this->stateRepo->findAll();
        foreach ($states as $state) {
            $balance = (int) $state->getCurrentBalance();
            $cockpitId = $state->getCockpit()->getId();

            $activeQueueCount = (int) $this->em->getConnection()->fetchOne(
                "SELECT COUNT(id) FROM production_queue WHERE cockpit_id = :id AND status IN ('pending', 'selected', 'in_production')",
                ['id' => $cockpitId]
            );

            if ($balance > 0 && $activeQueueCount === 0) {
                $io->error(sprintf(
                    "Cockpit '%s' has positive balance (+%d) but NO active queue entry.",
                    $state->getCockpit()->getCockpitCode(),
                    $balance
                ));
                $discrepancies++;
            }

            if ($balance <= 0 && $activeQueueCount > 0) {
                $io->error(sprintf(
                    "Cockpit '%s' has balance (%d) but has %d ACTIVE queue entries.",
                    $state->getCockpit()->getCockpitCode(),
                    $balance,
                    $activeQueueCount
                ));
                $discrepancies++;
            }

            if ($activeQueueCount > 1) {
                $io->error(sprintf(
                    "Cockpit '%s' has duplicate active queue entries (%d).",
                    $state->getCockpit()->getCockpitCode(),
                    $activeQueueCount
                ));
                $discrepancies++;
            }
        }

        if ($discrepancies === 0) {
            $io->success('FIFO Queue is perfectly consistent with Cockpit State math.');
        } else {
            $io->warning(sprintf('Found %d queue discrepancies. Manual intervention required.', $discrepancies));
        }

        return Command::SUCCESS;
    }
}
