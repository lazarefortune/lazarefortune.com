<?php

declare(strict_types=1);

namespace App\Video\Repository;

use App\Content\Enum\PublicationStatus;
use App\Video\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function findPublishedBySlug(string $slug): ?Video
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => PublicationStatus::PUBLISHED,
        ]);
    }

    /**
     * @return list<Video>
     */
    public function findLatestForStudio(int $limit = 50): array
    {
        /** @var list<Video> $results */
        $results = $this->createQueryBuilder('video')
            ->orderBy('video.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
