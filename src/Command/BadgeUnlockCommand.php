<?php

namespace App\Command;

use App\Domain\Badge\BadgeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:badge-unlock')]
class BadgeUnlockCommand extends Command
{
    public function __construct(
    )
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setDescription( 'Unlock a badge' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int
    {
        $io = new SymfonyStyle( $input, $output );
        $this->unlock( $io, $input, $output );
        return Command::SUCCESS;
    }

    public function unlock( SymfonyStyle $io, InputInterface $input, OutputInterface $output ) : void
    {
        $io->success( 'Badge unlocked' );
    }
}