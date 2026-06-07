<?php

declare(strict_types=1);

namespace App\Auth\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:users:import-legacy',
    description: 'Import users from the legacy database into V3',
)]
final class ImportLegacyUsersCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'legacy-dsn',
                null,
                InputOption::VALUE_REQUIRED,
                'Legacy database DSN (defaults to LEGACY_DATABASE_URL env var when implemented)',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simulate the import without writing to the V3 database',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Import legacy users');
        $io->warning([
            'This command is not implemented yet.',
            'It will connect to the legacy database, map old users to App\\Auth\\Entity\\User,',
            'preserve legacyId for traceability, migrate password hashes as-is, and map roles',
            '(ROLE_AUTHOR will be dropped, ROLE_ADMIN / ROLE_SUPER_ADMIN kept).',
        ]);

        if ($input->getOption('dry-run')) {
            $io->note('Dry-run mode will be supported when the import is implemented.');
        }

        return Command::SUCCESS;
    }
}
