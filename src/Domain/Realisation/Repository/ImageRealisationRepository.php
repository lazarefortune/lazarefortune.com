<?php

namespace App\Domain\Realisation\Repository;

use App\Domain\Realisation\Entity\ImageRealisation;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<ImageRealisation>
 */
class ImageRealisationRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, ImageRealisation::class );
    }
}
