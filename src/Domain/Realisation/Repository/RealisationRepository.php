<?php

namespace App\Domain\Realisation\Repository;

use App\Domain\Realisation\Entity\Realisation;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Realisation>
 */
class RealisationRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Realisation::class );
    }

    public function countRealisations() : int
    {
        return $this->createQueryBuilder( 'r' )
            ->select( 'COUNT(r)' )
            ->getQuery()
            ->getSingleScalarResult();
    }
}
