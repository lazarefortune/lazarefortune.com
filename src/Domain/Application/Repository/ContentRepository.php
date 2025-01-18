<?php

namespace App\Domain\Application\Repository;

use App\Domain\Application\Entity\Content;
use App\Infrastructure\Orm\AbstractRepository;
use App\Infrastructure\Orm\IterableQueryBuilder;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Content>
 */
class ContentRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('c')
            ->where('c.online = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return IterableQueryBuilder<Content>
     */
    public function findLatest(int $limit = 5, bool $withPremium = true): IterableQueryBuilder
    {
        $queryBuilder = $this->createIterableQuery('c')
            ->orderBy('c.createdAt', 'DESC')
            ->where('c.online = TRUE')
            ->setMaxResults($limit);

        if (!$withPremium) {
            $date = new \DateTimeImmutable('+ 3 days');
            $queryBuilder = $queryBuilder
                ->andWhere('c.createdAt < :published_at')
                ->setParameter('published_at', $date, Types::DATETIME_IMMUTABLE);
        }

        return $queryBuilder;
    }

    public function findLatest2(int $limit = 5, bool $withPremium = true) : array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.online = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($limit);

        if (!$withPremium) {
            $qb->andWhere('c.premium = false');
        }

        return $qb->getQuery()->getResult();
    }

    public function findLatestPublished(int $limit = 5) : array
    {
        return $this->createQueryBuilder('c')
            ->where('c.online = true')
            ->andWhere('c.publishedAt < NOW()')
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function search(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}