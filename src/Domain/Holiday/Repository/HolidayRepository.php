<?php

namespace App\Domain\Holiday\Repository;

use App\Domain\Holiday\Entity\Holiday;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Holiday>
 */
class HolidayRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Holiday::class );
    }

    /**
     * @return array<Holiday>
     */
    public function findHolidayBetweenDates( \DateTimeInterface $startDate, \DateTimeInterface $endDate ) : array
    {
        return $this->createQueryBuilder( 'h' )
            ->andWhere( 'h.startDate BETWEEN :startDate AND :endDate' )
            ->orWhere( 'h.endDate BETWEEN :startDate AND :endDate' )
            ->setParameter( 'startDate', $startDate )
            ->setParameter( 'endDate', $endDate )
            ->getQuery()
            ->getResult();
    }
}
