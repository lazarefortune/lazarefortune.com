<?php

namespace App\Command;

use App\Domain\Newsletter\Service\NewsletterSendingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-newsletters')]
class SendNewslettersCommand extends Command
{
    public function __construct(
        private readonly NewsletterSendingService $newsletterSendingService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie les newsletters planifiées');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->newsletterSendingService->sendScheduledNewsletters();
        $output->writeln('<info>Envoi des newsletters terminé.</info>');

        return Command::SUCCESS;
    }
}
