<?php

namespace App\Domain\Appointment\Repository;

use App\Domain\Appointment\Entity\Appointment;
use App\Helper\Paginator\PaginatorInterface;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends AbstractRepository<Appointment>
 */
class AppointmentRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry, private readonly PaginatorInterface $paginator )
    {
        parent::__construct( $registry, Appointment::class );
    }

    public function findAppointmentPaginated( int $page = 1, int $limit = 10 ) : \Knp\Component\Pager\Pagination\PaginationInterface
    {
        $query = $this->createQueryBuilder( 'b' )
            ->orderBy( 'b.date', 'DESC' )
            ->addOrderBy( 'b.startTime', 'DESC' )
            ->setMaxResults( $limit )
            ->getQuery();
        $this->paginator->allowSort( 'b.id', 'b.date', 'b.startTime', 'b.endTime' );
        return $this->paginator->paginate( $query );
    }

    public function findAllOrderedByDate() : array
    {
        // join slot date start end for order
        return $this->createQueryBuilder( 'b' )
            ->orderBy( 'b.date', 'ASC' )
            ->addOrderBy( 'b.startTime', 'ASC' )
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Appointment[]
     */
    public function findReservedAppointments() : array
    {
        return $this->createQueryBuilder( 'b' )
            ->andWhere( 'b.status = :val' )
            ->setParameter( 'val', Appointment::STATUS_CONFIRMED )
            ->orderBy( 'b.date', 'ASC' )
            ->addOrderBy( 'b.time', 'ASC' )
            ->getQuery()
            ->getResult();
    }

    public function countAppointments() : int
    {
        return $this->createQueryBuilder( 'b' )
            ->select( 'COUNT(b)' )
            ->getQuery()
            ->getSingleScalarResult();
    }
}
