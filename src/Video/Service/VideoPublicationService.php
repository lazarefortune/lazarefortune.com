<?php

declare(strict_types=1);

namespace App\Video\Service;

use App\Content\Enum\PublicationStatus;
use App\Video\Entity\Video;
use App\Video\Exception\InvalidPublicationScheduleException;
use Doctrine\ORM\EntityManagerInterface;

final class VideoPublicationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function publishNow(Video $video): void
    {
        $now = new \DateTimeImmutable();

        $video
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt($now)
            ->setScheduledAt(null);

        $this->entityManager->flush();
    }

    public function schedule(Video $video, \DateTimeImmutable $scheduledAt): void
    {
        $now = new \DateTimeImmutable();
        if ($scheduledAt <= $now) {
            throw new InvalidPublicationScheduleException('La date de publication doit etre dans le futur.');
        }

        $video
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt($scheduledAt)
            ->setPublishedAt(null);

        $this->entityManager->flush();
    }

    public function markAsDraft(Video $video): void
    {
        $video
            ->setStatus(PublicationStatus::DRAFT)
            ->setScheduledAt(null)
            ->setPublishedAt(null);

        $this->entityManager->flush();
    }

    public function archive(Video $video): void
    {
        $video
            ->setStatus(PublicationStatus::ARCHIVED)
            ->setScheduledAt(null);

        $this->entityManager->flush();
    }
}
