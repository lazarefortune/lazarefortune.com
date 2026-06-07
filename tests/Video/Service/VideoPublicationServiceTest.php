<?php

declare(strict_types=1);

namespace App\Tests\Video\Service;

use App\Auth\Entity\User;
use App\Content\Enum\PublicationStatus;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;
use App\Video\Exception\InvalidPublicationScheduleException;
use App\Video\Service\VideoPublicationService;

final class VideoPublicationServiceTest extends EditorialDoctrineTestCase
{
    public function testPublishNowSetsPublishedStatusAndClearsSchedule(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager, PublicationStatus::DRAFT);

        $before = new \DateTimeImmutable();
        $this->service()->publishNow($video);
        $after = new \DateTimeImmutable();

        $this->assertSame(PublicationStatus::PUBLISHED, $video->getStatus());
        $this->assertNull($video->getScheduledAt());
        $this->assertNotNull($video->getPublishedAt());
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $video->getPublishedAt()?->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $video->getPublishedAt()?->getTimestamp());
    }

    public function testScheduleSetsScheduledStatusWithFutureDate(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager, PublicationStatus::DRAFT);
        $scheduledAt = new \DateTimeImmutable('+2 days');

        $this->service()->schedule($video, $scheduledAt);

        $this->assertSame(PublicationStatus::SCHEDULED, $video->getStatus());
        $this->assertSame($scheduledAt->getTimestamp(), $video->getScheduledAt()?->getTimestamp());
        $this->assertNull($video->getPublishedAt());
    }

    public function testScheduleRejectsPastDate(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager, PublicationStatus::DRAFT);

        $this->expectException(InvalidPublicationScheduleException::class);
        $this->service()->schedule($video, new \DateTimeImmutable('-1 hour'));
    }

    public function testMarkAsDraftClearsPublicationDates(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager, PublicationStatus::PUBLISHED);
        $video
            ->setPublishedAt(new \DateTimeImmutable('-1 day'))
            ->setScheduledAt(new \DateTimeImmutable('+1 day'));
        $entityManager->flush();

        $this->service()->markAsDraft($video);

        $this->assertSame(PublicationStatus::DRAFT, $video->getStatus());
        $this->assertNull($video->getScheduledAt());
        $this->assertNull($video->getPublishedAt());
    }

    public function testArchiveKeepsPublishedAtAndClearsSchedule(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager, PublicationStatus::PUBLISHED);
        $publishedAt = new \DateTimeImmutable('-3 days');
        $video
            ->setPublishedAt($publishedAt)
            ->setScheduledAt(new \DateTimeImmutable('+1 day'));
        $entityManager->flush();

        $this->service()->archive($video);

        $this->assertSame(PublicationStatus::ARCHIVED, $video->getStatus());
        $this->assertNull($video->getScheduledAt());
        $this->assertSame($publishedAt->getTimestamp(), $video->getPublishedAt()?->getTimestamp());
    }

    private function service(): VideoPublicationService
    {
        return static::getContainer()->get(VideoPublicationService::class);
    }

    private function persistVideo(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        PublicationStatus $status,
    ): Video {
        $author = (new User())
            ->setEmail(sprintf('video-publication-%s@example.com', uniqid('', true)))
            ->setPassword('hashed-password')
            ->setRoles([User::ROLE_ADMIN]);
        $entityManager->persist($author);

        $video = (new Video($author))
            ->setTitle('Video publication service')
            ->setSlug(sprintf('video-publication-service-%s', uniqid('', true)))
            ->setStatus($status);
        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
