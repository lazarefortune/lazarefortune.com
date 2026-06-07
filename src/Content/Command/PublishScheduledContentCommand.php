<?php

declare(strict_types=1);

namespace App\Content\Command;

use App\Content\Service\ScheduledPublicationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:content:publish-scheduled',
    description: 'Publish scheduled contents and playlists whose scheduledAt has passed',
)]
final class PublishScheduledContentCommand extends Command
{
    public function __construct(
        private readonly ScheduledPublicationService $scheduledPublicationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List what would be published without writing')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum items per resource type', '100');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $limit = (int) $input->getOption('limit');

        if ($limit < 1) {
            $io->error('The --limit option must be at least 1.');

            return Command::FAILURE;
        }

        $now = new \DateTimeImmutable();
        $result = $this->scheduledPublicationService->publishDue($now, $dryRun, $limit);

        if ($dryRun) {
            $io->note('Dry run: no database changes were made.');
        }

        $io->success(sprintf(
            '%s %d content(s) and %d playlist(s).',
            $dryRun ? 'Would publish' : 'Published',
            $result->publishedContents,
            $result->publishedPlaylists,
        ));

        if ($result->total() === 0) {
            $io->writeln('No scheduled resources were ready for publication.');
        }

        return Command::SUCCESS;
    }
}
