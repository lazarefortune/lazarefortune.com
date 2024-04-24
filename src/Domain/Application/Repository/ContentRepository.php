<?php

namespace App\Domain\Application\Repository;

use App\Domain\Application\Entity\Content;
use App\Infrastructure\Orm\AbstractRepository;
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

    public function findLatest(int $limit = 5, bool $withPremium = true) : array
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
}