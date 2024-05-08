<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\Course;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Course>
 */
class CourseRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function queryAll( $isUserPremium = true ) : QueryBuilder
    {
        $date = new \DateTimeImmutable( '+ 3 days' );
        $queryBuilder = $this->createQueryBuilder( 'c' )
            ->where('c.online = true')
            ->orderBy('c.publishedAt', 'DESC');

        if ( !$isUserPremium ) {
            $queryBuilder
                ->andWhere('c.publishedAt <= :date')
                ->setParameter('date', $date);
        }

        return $queryBuilder;
    }
}