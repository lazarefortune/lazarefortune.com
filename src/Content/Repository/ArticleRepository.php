<?php

declare(strict_types=1);

namespace App\Content\Repository;

use App\Content\Entity\Article;
use App\Content\Enum\PublicationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => PublicationStatus::PUBLISHED,
        ]);
    }

    /**
     * @return list<Article>
     */
    public function findLatestForStudio(int $limit = 50): array
    {
        /** @var list<Article> $results */
        $results = $this->createQueryBuilder('article')
            ->orderBy('article.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
