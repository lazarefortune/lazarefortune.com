<?php

namespace App\Domain\Youtube\Repository;

use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<YoutubeSetting>
 */
class YoutubeSettingRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, YoutubeSetting::class );
    }
}