<?php

declare(strict_types=1);

namespace App\Progress\Service;

use App\Auth\Entity\User;
use App\Content\Entity\Content;
use App\Progress\Entity\ContentProgress;
use App\Progress\Exception\InvalidProgressValueException;
use App\Progress\Repository\ContentProgressRepository;
use App\Video\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

final class ProgressService
{
    public function __construct(
        private readonly ContentProgressRepository $contentProgressRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function updateVideoProgress(
        User $user,
        Video $video,
        int $percent,
        ?int $positionSeconds = null,
    ): ContentProgress {
        $this->assertValidPercent($percent);
        $this->assertValidPositionSeconds($positionSeconds);

        $progress = $this->getOrCreate($user, $video);
        $progress
            ->setPercent($percent)
            ->setLastPositionSeconds($positionSeconds);

        $this->applyCompletionState($progress, $percent);

        $this->entityManager->persist($progress);
        $this->entityManager->flush();

        return $progress;
    }

    public function markContentCompleted(User $user, Content $content): ContentProgress
    {
        $progress = $this->getOrCreate($user, $content);
        $progress
            ->setPercent(100)
            ->setLastPositionSeconds(null)
            ->setCompletedAt(new \DateTimeImmutable());

        $this->entityManager->persist($progress);
        $this->entityManager->flush();

        return $progress;
    }

    public function resetProgress(User $user, Content $content): void
    {
        $progress = $this->contentProgressRepository->findOneForUserAndContent($user, $content);

        if ($progress === null) {
            return;
        }

        $this->entityManager->remove($progress);
        $this->entityManager->flush();
    }

    private function getOrCreate(User $user, Content $content): ContentProgress
    {
        return $this->contentProgressRepository->findOneForUserAndContent($user, $content)
            ?? new ContentProgress($user, $content);
    }

    private function applyCompletionState(ContentProgress $progress, int $percent): void
    {
        if ($percent < 100) {
            return;
        }

        $progress->setPercent(100);

        if ($progress->getCompletedAt() === null) {
            $progress->setCompletedAt(new \DateTimeImmutable());
        }
    }

    private function assertValidPercent(int $percent): void
    {
        if ($percent < 0 || $percent > 100) {
            throw new InvalidProgressValueException(sprintf(
                'Progress percent must be between 0 and 100, got %d.',
                $percent,
            ));
        }
    }

    private function assertValidPositionSeconds(?int $positionSeconds): void
    {
        if ($positionSeconds !== null && $positionSeconds < 0) {
            throw new InvalidProgressValueException(sprintf(
                'Last position seconds cannot be negative, got %d.',
                $positionSeconds,
            ));
        }
    }
}
