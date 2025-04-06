<?php

namespace App\Domain\Feedback\Repository;

use App\Domain\Feedback\Entity\Feedback;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Feedback>
 */
class FeedbackRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Feedback::class );
    }

    /**
     * Find latest feedback order by createdAt
     *
     * @param int $limit
     * @return Query
     */
    public function findLatestQuery( int $limit = 10 ) : Query
    {
        $qb = $this->createQueryBuilder( 'c' );
        return $qb
            ->orderBy( 'c.createdAt', 'DESC' )
            ->setMaxResults( $limit )
            ->getQuery();
    }
}
