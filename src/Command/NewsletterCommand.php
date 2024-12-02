<?php

namespace App\Command;

use App\Domain\Newsletter\Service\NewsletterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:newsletter',
    description: 'Commande d\'envoi de la newsletter',
)]
class NewsletterCommand extends Command
{

    public function __construct( private readonly NewsletterService $newsletterService )
    {

        parent::__construct();
    }

    public function execute( InputInterface $input, OutputInterface $output ) : int
    {
        $output->writeln( 'Envoi de la newsletter...' );

        $this->newsletterService->sendNewsletters();

        $output->writeln( 'Newsletter envoyée !' );
        return Command::SUCCESS;
    }
}