<?php

declare(strict_types=1);

namespace App\Comment\Service;

use App\Auth\Entity\User;
use App\Comment\Entity\Comment;
use App\Comment\Enum\CommentStatus;
use App\Comment\Exception\InvalidCommentException;
use App\Content\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;

final class CommentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createComment(User $author, Content $content, string $body): Comment
    {
        $comment = new Comment($author, $content, $this->normalizeBody($body));

        return $this->persist($comment);
    }

    public function replyToComment(User $author, Comment $parent, string $body): Comment
    {
        $comment = new Comment($author, $parent->getContent(), $this->normalizeBody($body));
        $comment->setParent($parent);
        $this->assertParentBelongsToSameContent($comment);

        return $this->persist($comment);
    }

    public function hideComment(Comment $comment): Comment
    {
        $comment->setStatus(CommentStatus::HIDDEN);

        return $this->persist($comment);
    }

    public function markDeleted(Comment $comment): Comment
    {
        $comment->setStatus(CommentStatus::DELETED);

        return $this->persist($comment);
    }

    private function persist(Comment $comment): Comment
    {
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    private function normalizeBody(string $body): string
    {
        $normalized = trim($body);

        if ($normalized === '') {
            throw new InvalidCommentException('Comment body cannot be empty.');
        }

        return $normalized;
    }

    private function assertParentBelongsToSameContent(Comment $comment): void
    {
        $parent = $comment->getParent();

        if ($parent === null) {
            return;
        }

        if ($parent->getContent()->getId() !== $comment->getContent()->getId()) {
            throw new InvalidCommentException('Parent comment must belong to the same content.');
        }
    }
}
