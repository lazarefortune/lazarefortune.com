<?php

declare(strict_types=1);

namespace App\Content\Repository;

use App\Content\Entity\Content;
use App\Content\Enum\PublicationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Content>
 */
class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    public function findPublishedBySlug(string $slug): ?Content
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => PublicationStatus::PUBLISHED,
        ]);
    }

    public function findOneBySlug(string $slug): ?Content
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return list<Content>
     */
    public function findScheduledReadyForPublication(\DateTimeImmutable $now, int $limit = 100): array
    {
        /** @var list<Content> $results */
        $results = $this->createQueryBuilder('content')
            ->where('content.status = :scheduled')
            ->andWhere('content.scheduledAt IS NOT NULL')
            ->andWhere('content.scheduledAt <= :now')
            ->setParameter('scheduled', PublicationStatus::SCHEDULED)
            ->setParameter('now', $now)
            ->orderBy('content.scheduledAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
