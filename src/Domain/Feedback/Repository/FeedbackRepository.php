<?php

namespace App\Domain\Feedback\Repository;

use App\Domain\Feedback\Entity\Feedback;
use App\Infrastructure\Orm\AbstractRepository;
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
}
