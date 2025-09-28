<?php

namespace App\Domain\Quiz\Repository;

use App\Domain\Quiz\Entity\QuizSession;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<QuizSession>
 */
class QuizSessionRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizSession::class);
    }
}