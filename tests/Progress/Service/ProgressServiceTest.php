<?php

declare(strict_types=1);

namespace App\Tests\Progress\Service;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Progress\Exception\InvalidProgressValueException;
use App\Progress\Repository\ContentProgressRepository;
use App\Progress\Service\ProgressService;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class ProgressServiceTest extends EditorialDoctrineTestCase
{
    public function testCreatesProgressOnFirstVideoUpdate(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);

        $progress = $this->service()->updateVideoProgress($user, $video, 42, 120);

        $this->assertSame(42, $progress->getPercent());
        $this->assertSame(120, $progress->getLastPositionSeconds());
        $this->assertNull($progress->getCompletedAt());
        $this->assertNotNull($progress->getId());
    }

    public function testUpdatesExistingVideoProgressWithPosition(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);
        $service = $this->service();

        $service->updateVideoProgress($user, $video, 10, 30);
        $entityManager->clear();

        $progress = $service->updateVideoProgress($user, $video, 75, 540);

        $this->assertSame(75, $progress->getPercent());
        $this->assertSame(540, $progress->getLastPositionSeconds());
    }

    public function testInvalidPercentThrowsException(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);

        $this->expectException(InvalidProgressValueException::class);
        $this->service()->updateVideoProgress($user, $video, 101, 0);
    }

    public function testNegativePositionThrowsException(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);

        $this->expectException(InvalidProgressValueException::class);
        $this->service()->updateVideoProgress($user, $video, 10, -1);
    }

    public function testCompletedAtIsSetWhenPercentReaches100(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);

        $progress = $this->service()->updateVideoProgress($user, $video, 100, 900);

        $this->assertSame(100, $progress->getPercent());
        $this->assertNotNull($progress->getCompletedAt());
    }

    public function testCompletedAtIsPreservedWhenPercentDropsBelow100AfterCompletion(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);
        $service = $this->service();

        $service->updateVideoProgress($user, $video, 100, 900);
        $entityManager->clear();

        $completedAt = $this->repository()->findOneForUserAndContent($user, $video)?->getCompletedAt();
        $progress = $service->updateVideoProgress($user, $video, 95, 850);

        $this->assertSame(95, $progress->getPercent());
        $this->assertNotNull($progress->getCompletedAt());
        $this->assertEquals($completedAt, $progress->getCompletedAt());
    }

    public function testMarkContentCompletedSetsPercentAndCompletedAt(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $article = (new Article($user))
            ->setTitle('Article progress')
            ->setSlug('article-progress')
            ->setBody('Body');
        $entityManager->persist($article);
        $entityManager->flush();

        $progress = $this->service()->markContentCompleted($user, $article);

        $this->assertSame(100, $progress->getPercent());
        $this->assertNotNull($progress->getCompletedAt());
        $this->assertNull($progress->getLastPositionSeconds());
    }

    public function testUniqueUserContentConstraint(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);

        $entityManager->persist(new \App\Progress\Entity\ContentProgress($user, $video));
        $entityManager->flush();

        $entityManager->persist(new \App\Progress\Entity\ContentProgress($user, $video));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testResetProgressRemovesRecord(): void
    {
        $entityManager = $this->entityManager();
        $user = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $user);
        $service = $this->service();

        $service->updateVideoProgress($user, $video, 50, 200);
        $service->resetProgress($user, $video);

        $repository = $this->repository();
        $this->assertNull($repository->findOneForUserAndContent($user, $video));
    }

    private function service(): ProgressService
    {
        return new ProgressService($this->repository(), $this->entityManager());
    }

    private function repository(): ContentProgressRepository
    {
        /** @var ContentProgressRepository $repository */
        $repository = $this->entityManager()->getRepository(\App\Progress\Entity\ContentProgress::class);

        return $repository;
    }

    private function persistUser(\Doctrine\ORM\EntityManagerInterface $entityManager): User
    {
        $user = (new User())
            ->setEmail(sprintf('progress-user-%s@example.com', uniqid('', true)))
            ->setPassword('hashed-password');
        $entityManager->persist($user);

        return $user;
    }

    private function persistVideo(\Doctrine\ORM\EntityManagerInterface $entityManager, User $user): Video
    {
        $video = (new Video($user))
            ->setTitle('Progress video')
            ->setSlug(sprintf('progress-video-%s', uniqid('', true)));
        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
