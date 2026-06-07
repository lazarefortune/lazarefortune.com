<?php

declare(strict_types=1);

namespace App\Playlist\Repository;

use App\Content\Enum\PublicationStatus;
use App\Playlist\Entity\Playlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Playlist>
 */
class PlaylistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    public function findPublishedBySlug(string $slug): ?Playlist
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => PublicationStatus::PUBLISHED,
        ]);
    }

    /**
     * @return list<Playlist>
     */
    public function findScheduledReadyForPublication(\DateTimeImmutable $now, int $limit = 100): array
    {
        /** @var list<Playlist> $results */
        $results = $this->createQueryBuilder('playlist')
            ->where('playlist.status = :scheduled')
            ->andWhere('playlist.scheduledAt IS NOT NULL')
            ->andWhere('playlist.scheduledAt <= :now')
            ->setParameter('scheduled', PublicationStatus::SCHEDULED)
            ->setParameter('now', $now)
            ->orderBy('playlist.scheduledAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
