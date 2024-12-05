<?php

namespace App\Domain\Quiz\Repository;

use App\Domain\Quiz\Entity\QuizResult;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<QuizResult>
 */
class QuizResultRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizResult::class);
    }
}
