<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Technology;
use App\Domain\Course\Entity\TechnologyUsage;
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

    public function queryForTechnology( Technology $technology ) : \Doctrine\ORM\Query
    {
        $courseClass = Course::class;
        $usageClass = TechnologyUsage::class;

        return $this->getEntityManager()->createQuery(<<<DQL
            SELECT c
            FROM $courseClass c
            JOIN c.technologyUsages ct WITH ct.technology = :technology AND ct.secondary = false
            WHERE NOT EXISTS (
                SELECT t FROM $usageClass t WHERE t.content = c.formation AND t.technology = :technology
            )
            AND c.online = true
            ORDER BY c.createdAt DESC
        DQL
        )->setParameter('technology', $technology)->setMaxResults(10);
    }
}