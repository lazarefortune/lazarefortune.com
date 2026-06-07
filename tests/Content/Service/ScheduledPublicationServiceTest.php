<?php

declare(strict_types=1);

namespace App\Tests\Content\Service;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Content\Enum\PublicationStatus;
use App\Content\Service\ScheduledPublicationService;
use App\Playlist\Entity\Playlist;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;

final class ScheduledPublicationServiceTest extends EditorialDoctrineTestCase
{
    public function testPastScheduledContentBecomesPublished(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistAuthor($entityManager);

        $video = (new Video($author))
            ->setTitle('Past video')
            ->setSlug('past-video')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-2 hours'));
        $entityManager->persist($video);
        $entityManager->flush();

        $service = $this->service();
        $result = $service->publishDue(new \DateTimeImmutable(), dryRun: false);

        $this->assertSame(1, $result->publishedContents);
        $this->assertSame(0, $result->publishedPlaylists);

        $entityManager->clear();
        $reloaded = $entityManager->find(Video::class, $video->getId());

        $this->assertSame(PublicationStatus::PUBLISHED, $reloaded?->getStatus());
        $this->assertNotNull($reloaded?->getPublishedAt());
        $this->assertNull($reloaded?->getScheduledAt());
    }

    public function testFutureScheduledContentRemainsScheduled(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistAuthor($entityManager);

        $article = (new Article($author))
            ->setTitle('Future article')
            ->setSlug('future-article')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('+2 days'));
        $entityManager->persist($article);
        $entityManager->flush();

        $result = $this->service()->publishDue(new \DateTimeImmutable(), dryRun: false);

        $this->assertSame(0, $result->publishedContents);

        $entityManager->clear();
        $reloaded = $entityManager->find(Article::class, $article->getId());

        $this->assertSame(PublicationStatus::SCHEDULED, $reloaded?->getStatus());
        $this->assertNull($reloaded?->getPublishedAt());
    }

    public function testPastScheduledPlaylistBecomesPublished(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistAuthor($entityManager);

        $playlist = (new Playlist($author))
            ->setTitle('Past playlist')
            ->setSlug('past-playlist')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-30 minutes'));
        $entityManager->persist($playlist);
        $entityManager->flush();

        $result = $this->service()->publishDue(new \DateTimeImmutable(), dryRun: false);

        $this->assertSame(0, $result->publishedContents);
        $this->assertSame(1, $result->publishedPlaylists);

        $entityManager->clear();
        $reloaded = $entityManager->find(Playlist::class, $playlist->getId());

        $this->assertSame(PublicationStatus::PUBLISHED, $reloaded?->getStatus());
        $this->assertNotNull($reloaded?->getPublishedAt());
        $this->assertNull($reloaded?->getScheduledAt());
    }

    public function testDryRunDoesNotPersistChanges(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistAuthor($entityManager);

        $video = (new Video($author))
            ->setTitle('Dry run video')
            ->setSlug('dry-run-video')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-1 hour'));
        $entityManager->persist($video);
        $entityManager->flush();
        $entityManager->clear();

        $result = $this->service()->publishDue(new \DateTimeImmutable(), dryRun: true);

        $this->assertSame(1, $result->publishedContents);

        $reloaded = $entityManager->find(Video::class, $video->getId());
        $this->assertSame(PublicationStatus::SCHEDULED, $reloaded?->getStatus());
        $this->assertNull($reloaded?->getPublishedAt());
    }

    private function service(): ScheduledPublicationService
    {
        /** @var ScheduledPublicationService $service */
        $service = static::getContainer()->get(ScheduledPublicationService::class);

        return $service;
    }

    private function persistAuthor(\Doctrine\ORM\EntityManagerInterface $entityManager): User
    {
        $author = (new User())
            ->setEmail(sprintf('scheduled-author-%s@example.com', uniqid('', true)))
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        return $author;
    }
}
