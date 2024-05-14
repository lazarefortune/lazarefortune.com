<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Formation>
 */
class FormationRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('f')
            ->where('f.online = true')
            ->orderBy('f.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findForTechnology(Technology $technology): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.technologyUsages', 'usage')
            ->where('f.online = true')
            ->andWhere('usage.technology = :technology')
            ->andWhere('usage.secondary = false')
            ->setParameter('technology', $technology)
            ->orderBy('f.publishedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTechnology( Technology $technology )
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.technologyUsages', 'usage')
            ->where('f.online = true')
            ->andWhere('usage.technology = :technology')
            ->andWhere('usage.secondary = false')
            ->setParameter('technology', $technology)
            ->orderBy('f.publishedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}