<?php

namespace App\Domain\Prestation\Repository;

use App\Domain\Prestation\Entity\Prestation;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Prestation>
 */
class PrestationRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Prestation::class );
    }
}
