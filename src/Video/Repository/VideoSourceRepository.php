<?php

declare(strict_types=1);

namespace App\Video\Repository;

use App\Video\Entity\VideoSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoSource>
 */
class VideoSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoSource::class);
    }
}
