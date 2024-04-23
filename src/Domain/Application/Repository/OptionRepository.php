<?php

namespace App\Domain\Application\Repository;

use App\Domain\Application\Entity\Option;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Option>
 */
class OptionRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Option::class );
    }

//    public function findAllForTwig() : array
//    {
//        return $this->createQueryBuilder( 'o', 'o.name' )
//            ->select( 'o.name', 'o.value' )
//            ->getQuery()
//            ->getArrayResult();
//    }

    /**
     * @return array<Option>
     */
    public function findAllForTwig() : array
    {
        return $this->createQueryBuilder( 'o', 'o.name' )
            ->getQuery()
            ->getResult();
    }
}
