<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\Formation;
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
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}