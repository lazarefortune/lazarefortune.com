<?php

declare(strict_types=1);

namespace App\Tests\Comment\Service;

use App\Auth\Entity\User;
use App\Comment\Enum\CommentStatus;
use App\Comment\Exception\InvalidCommentException;
use App\Comment\Repository\CommentRepository;
use App\Comment\Service\CommentService;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;

final class CommentServiceTest extends EditorialDoctrineTestCase
{
    public function testCreatesComment(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);

        $comment = $this->service()->createComment($author, $video, 'Great tutorial!');

        $this->assertNotNull($comment->getId());
        $this->assertSame('Great tutorial!', $comment->getBody());
        $this->assertSame($author, $comment->getAuthor());
        $this->assertSame($video, $comment->getContent());
        $this->assertSame(CommentStatus::PUBLISHED, $comment->getStatus());
        $this->assertNull($comment->getParent());
    }

    public function testEmptyBodyIsRejected(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);

        $this->expectException(InvalidCommentException::class);
        $this->service()->createComment($author, $video, '   ');
    }

    public function testBodyIsTrimmed(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);

        $comment = $this->service()->createComment($author, $video, '  Hello world  ');

        $this->assertSame('Hello world', $comment->getBody());
    }

    public function testReplyKeepsSameContentAsParent(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);
        $service = $this->service();

        $parent = $service->createComment($author, $video, 'Parent comment');
        $reply = $service->replyToComment($author, $parent, 'Reply comment');

        $this->assertSame($parent, $reply->getParent());
        $this->assertSame($video->getId(), $reply->getContent()->getId());
        $this->assertSame($parent->getContent()->getId(), $reply->getContent()->getId());
    }

    public function testHiddenCommentIsExcludedFromFindPublishedForContent(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);
        $service = $this->service();
        $repository = $this->repository();

        $comment = $service->createComment($author, $video, 'Visible comment');
        $service->hideComment($comment);

        $this->assertSame([], $repository->findPublishedForContent($video));
    }

    public function testDeletedCommentIsExcludedFromFindPublishedForContent(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);
        $service = $this->service();
        $repository = $this->repository();

        $comment = $service->createComment($author, $video, 'Deleted comment');
        $service->markDeleted($comment);

        $this->assertSame([], $repository->findPublishedForContent($video));
    }

    public function testCountPublishedForContent(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistUser($entityManager);
        $video = $this->persistVideo($entityManager, $author);
        $service = $this->service();
        $repository = $this->repository();

        $published = $service->createComment($author, $video, 'First');
        $service->createComment($author, $video, 'Second');
        $hidden = $service->createComment($author, $video, 'Hidden');
        $deleted = $service->createComment($author, $video, 'Deleted');

        $service->hideComment($hidden);
        $service->markDeleted($deleted);

        $this->assertSame(CommentStatus::PUBLISHED, $published->getStatus());
        $this->assertSame(2, $repository->countPublishedForContent($video));
    }

    private function service(): CommentService
    {
        return new CommentService($this->entityManager());
    }

    private function repository(): CommentRepository
    {
        /** @var CommentRepository $repository */
        $repository = $this->entityManager()->getRepository(\App\Comment\Entity\Comment::class);

        return $repository;
    }

    private function persistUser(\Doctrine\ORM\EntityManagerInterface $entityManager): User
    {
        $user = (new User())
            ->setEmail(sprintf('comment-user-%s@example.com', uniqid('', true)))
            ->setPassword('hashed-password');
        $entityManager->persist($user);

        return $user;
    }

    private function persistVideo(\Doctrine\ORM\EntityManagerInterface $entityManager, User $user): Video
    {
        $video = (new Video($user))
            ->setTitle('Comment video')
            ->setSlug(sprintf('comment-video-%s', uniqid('', true)));
        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
