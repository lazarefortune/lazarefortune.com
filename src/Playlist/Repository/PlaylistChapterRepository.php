<?php

declare(strict_types=1);

namespace App\Playlist\Repository;

use App\Playlist\Entity\PlaylistChapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaylistChapter>
 */
class PlaylistChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistChapter::class);
    }
}
