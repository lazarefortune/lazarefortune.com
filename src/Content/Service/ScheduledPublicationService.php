<?php

declare(strict_types=1);

namespace App\Content\Service;

use App\Content\Entity\Content;
use App\Content\Enum\PublicationStatus;
use App\Content\Repository\ContentRepository;
use App\Playlist\Entity\Playlist;
use App\Playlist\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ScheduledPublicationService
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly PlaylistRepository $playlistRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function publishDue(
        \DateTimeImmutable $now,
        bool $dryRun = false,
        int $limit = 100,
    ): ScheduledPublicationResult {
        $contents = $this->contentRepository->findScheduledReadyForPublication($now, $limit);
        $playlists = $this->playlistRepository->findScheduledReadyForPublication($now, $limit);

        if (!$dryRun) {
            foreach ($contents as $content) {
                $this->markAsPublished($content, $now);
            }

            foreach ($playlists as $playlist) {
                $this->markAsPublished($playlist, $now);
            }

            $this->entityManager->flush();
        }

        return new ScheduledPublicationResult(
            publishedContents: \count($contents),
            publishedPlaylists: \count($playlists),
        );
    }

    private function markAsPublished(Content|Playlist $resource, \DateTimeImmutable $now): void
    {
        $resource
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt($now)
            ->setScheduledAt(null);
    }
}
