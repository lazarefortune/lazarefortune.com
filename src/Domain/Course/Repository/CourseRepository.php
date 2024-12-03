<?php

namespace App\Domain\Course\Repository;

use App\Domain\Auth\Core\Entity\User;
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

    public function countOnlineCourses( User $user = null ) : int
    {
        $queryBuilder = $this->createQueryBuilder( 'c' )
            ->select('COUNT(c.id)')
            ->where('c.online = true');

        if ( $user ) {
            $queryBuilder
                ->andWhere('c.author = :user')
                ->setParameter('user', $user);
        }

        $query = $queryBuilder->getQuery();
        return (int) $query->getSingleScalarResult();
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

    public function getRecentVideos( $limit = 50 ) : array
    {
        $queryBuilder = $this->createQueryBuilder( 'c' )
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults( $limit );

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getTodayCourses(): array
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $todayStart = $date->setTime(0, 0, 0);
        $todayEnd = $date->setTime(23, 59, 59);

        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c.id', 'c.title', 'c.publishedAt', 'c.slug')
            ->where('c.publishedAt >= :todayStart')
            ->andWhere('c.publishedAt <= :todayEnd')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->orderBy('c.publishedAt', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}