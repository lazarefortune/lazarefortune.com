<?php

namespace App\Domain\Contact\Repository;

use App\Domain\Contact\Entity\Contact;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Contact>
 */
class ContactRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Contact::class );
    }

    /**
     * Find latest contact order by createdAt
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
