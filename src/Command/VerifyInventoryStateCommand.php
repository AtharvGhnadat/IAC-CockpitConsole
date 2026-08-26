<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CockpitStateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:verify-inventory-state',
    description: 'Verify mathematical consistency of Cockpit Inventory (Available Stock).',
)]
class VerifyInventoryStateCommand extends Command
{
    private CockpitStateRepository $cockpitStateRepo;
    private EntityManagerInterface $em;

    public function __construct(
        CockpitStateRepository $cockpitStateRepo,
        EntityManagerInterface $em,
    ) {
        parent::__construct();
        $this->cockpitStateRepo = $cockpitStateRepo;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Inventory State Verification');

        $states = $this->cockpitStateRepo->findAll();

        $discrepancies = 0;

        foreach ($states as $state) {
            $cockpitId = $state->getCockpit()->getId();

            // Calculate sum from production_events
            $productionSum = (int) $this->em->getConnection()->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) FROM production_events WHERE cockpit_id = :id',
                ['id' => $cockpitId],
            );

            // Calculate sum from dispatch_events
            $dispatchSum = (int) $this->em->getConnection()->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) FROM dispatch_events WHERE cockpit_id = :id',
                ['id' => $cockpitId],
            );

            $expectedAvailable = $productionSum - $dispatchSum;

            $actualProd = (int) $state->getTotalProduced();
            $actualDisp = (int) $state->getTotalDispatched();
            $actualAvail = (int) $state->getAvailableStock();

            $isCorrect = ($actualProd === $productionSum)
                         && ($actualDisp === $dispatchSum)
                         && ($actualAvail === $expectedAvailable)
                         && ($actualAvail >= 0);

            if (!$isCorrect) {
                ++$discrepancies;
                $io->error(\sprintf(
                    "Discrepancy for Cockpit '%s':\nExpected: Prod=%d, Disp=%d, Avail=%d\nActual  : Prod=%d, Disp=%d, Avail=%d",
                    $state->getCockpit()->getCockpitCode(),
                    $productionSum,
                    $dispatchSum,
                    $expectedAvailable,
                    $actualProd,
                    $actualDisp,
                    $actualAvail,
                ));
            }
        }

        if ($discrepancies === 0) {
            $io->success('All Cockpit States are mathematically verified against the inventory ledgers.');
        } else {
            $io->warning(\sprintf('Found %d discrepancies.', $discrepancies));
        }

        return Command::SUCCESS;
    }
}
