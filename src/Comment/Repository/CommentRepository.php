<?php

declare(strict_types=1);

namespace App\Comment\Repository;

use App\Auth\Entity\User;
use App\Comment\Entity\Comment;
use App\Comment\Enum\CommentStatus;
use App\Content\Entity\Content;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @return list<Comment>
     */
    public function findPublishedForContent(Content $content): array
    {
        /** @var list<Comment> $results */
        $results = $this->createQueryBuilder('comment')
            ->where('comment.content = :content')
            ->andWhere('comment.status = :status')
            ->setParameter('content', $content)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->orderBy('comment.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function countPublishedForContent(Content $content): int
    {
        return (int) $this->createQueryBuilder('comment')
            ->select('COUNT(comment.id)')
            ->where('comment.content = :content')
            ->andWhere('comment.status = :status')
            ->setParameter('content', $content)
            ->setParameter('status', CommentStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<Comment>
     */
    public function findRecentForAuthor(User $user, int $limit = 10): array
    {
        /** @var list<Comment> $results */
        $results = $this->createQueryBuilder('comment')
            ->where('comment.author = :author')
            ->setParameter('author', $user)
            ->orderBy('comment.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
