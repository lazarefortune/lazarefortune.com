<?php

namespace App\Domain\Collaboration\Event;

use App\Domain\Collaboration\Entity\CollaborationRequest;

class CollaborationRequestRejectedEvent
{
    public function __construct(
        private readonly CollaborationRequest $collaborationRequest
    )
    {
    }

    public function getCollaborationRequest(): CollaborationRequest
    {
        return $this->collaborationRequest;
    }
}