<?php

namespace App\Domain\Collaboration\Repository;

use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<CollaborationRequest>
 */
class CollaborationRequestRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, CollaborationRequest::class );
    }
}