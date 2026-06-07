<?php

declare(strict_types=1);

namespace App\Progress\Repository;

use App\Auth\Entity\User;
use App\Content\Entity\Content;
use App\Progress\Entity\ContentProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContentProgress>
 */
class ContentProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContentProgress::class);
    }

    public function findOneForUserAndContent(User $user, Content $content): ?ContentProgress
    {
        return $this->findOneBy([
            'user' => $user,
            'content' => $content,
        ]);
    }

    /**
     * @return list<ContentProgress>
     */
    public function findCompletedForUser(User $user): array
    {
        /** @var list<ContentProgress> $results */
        $results = $this->createQueryBuilder('progress')
            ->where('progress.user = :user')
            ->andWhere('progress.completedAt IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('progress.completedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * @return list<ContentProgress>
     */
    public function findRecentForUser(User $user, int $limit = 10): array
    {
        /** @var list<ContentProgress> $results */
        $results = $this->createQueryBuilder('progress')
            ->where('progress.user = :user')
            ->setParameter('user', $user)
            ->orderBy('progress.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
