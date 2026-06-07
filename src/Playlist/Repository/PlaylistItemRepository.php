<?php

declare(strict_types=1);

namespace App\Playlist\Repository;

use App\Playlist\Entity\PlaylistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaylistItem>
 */
class PlaylistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistItem::class);
    }
}
