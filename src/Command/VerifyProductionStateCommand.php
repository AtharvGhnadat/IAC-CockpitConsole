<?php

namespace App\Command;

use App\Repository\CockpitStateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cockpit:verify-production-state',
    description: 'Verify the mathematical consistency of Cockpit State vs Ledgers.',
)]
class VerifyProductionStateCommand extends Command
{
    private CockpitStateRepository $cockpitStateRepo;
    private EntityManagerInterface $em;

    public function __construct(
        CockpitStateRepository $cockpitStateRepo,
        EntityManagerInterface $em
    ) {
        parent::__construct();
        $this->cockpitStateRepo = $cockpitStateRepo;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Production State Verification');

        $states = $this->cockpitStateRepo->findAll();
        
        $discrepancies = 0;

        foreach ($states as $state) {
            $cockpitId = $state->getCockpit()->getId();
            
            // Calculate sum from request_events
            $requestSum = (int) $this->em->getConnection()->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) FROM request_events WHERE cockpit_id = :id',
                ['id' => $cockpitId]
            );

            // Calculate sum from production_events
            $productionSum = (int) $this->em->getConnection()->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) FROM production_events WHERE cockpit_id = :id',
                ['id' => $cockpitId]
            );

            $expectedBalance = $requestSum - $productionSum;

            $actualReq = (int) $state->getTotalRequested();
            $actualProd = (int) $state->getTotalProduced();
            $actualBal = (int) $state->getCurrentBalance();

            $isCorrect = ($actualReq === $requestSum) && 
                         ($actualProd === $productionSum) && 
                         ($actualBal === $expectedBalance);

            if (!$isCorrect) {
                $discrepancies++;
                $io->error(sprintf(
                    "Discrepancy for Cockpit '%s':\nExpected: Req=%d, Prod=%d, Bal=%d\nActual  : Req=%d, Prod=%d, Bal=%d",
                    $state->getCockpit()->getCockpitCode(),
                    $requestSum, $productionSum, $expectedBalance,
                    $actualReq, $actualProd, $actualBal
                ));
            }
        }

        if ($discrepancies === 0) {
            $io->success('All Cockpit States are mathematically verified against the ledgers.');
        } else {
            $io->warning(sprintf('Found %d discrepancies.', $discrepancies));
        }

        return Command::SUCCESS;
    }
}
