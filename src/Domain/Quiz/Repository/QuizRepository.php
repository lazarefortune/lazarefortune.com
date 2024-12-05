<?php

namespace App\Domain\Quiz\Repository;

use App\Domain\Quiz\Entity\Quiz;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Quiz>
 */
class QuizRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }
}