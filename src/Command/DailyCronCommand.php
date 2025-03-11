<?php

namespace App\Command;

use App\Domain\Premium\PremiumService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:daily-cron',
    description: 'Commande journalière'
)]
class DailyCronCommand extends Command
{
    public function __construct(
        private readonly PremiumService $premiumService,
    ) {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setDescription('Commande de nettoyage et notifications quotidiennes');
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int
    {
        $io = new SymfonyStyle( $input, $output );
        $this->cron( $io, $input, $output );
        return Command::SUCCESS;
    }

    public function cron( SymfonyStyle $io, InputInterface $input, OutputInterface $output ) : void
    {
        $countNotifiedUsers = $this->premiumService->notifyUsersPremiumExpiringSoon();

        if ($countNotifiedUsers > 0) {
            $io->success(sprintf('%d utilisateurs notifiés pour expiration de leur abonnement premium.', $countNotifiedUsers));
        } else {
            $io->info('Aucun utilisateur à notifier aujourd\'hui.');
        }

        $io->success('Daily cron executed');
    }

}