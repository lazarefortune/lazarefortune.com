<?php

namespace App\Domain\Collaboration\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Domain\Collaboration\Enum\CollaborationRequestRole;
use App\Domain\Collaboration\Enum\CollaborationRequestStatus;
use App\Domain\Collaboration\Event\CollaborationRequestAcceptedEvent;
use App\Domain\Collaboration\Event\CollaborationRequestCreatedEvent;
use App\Domain\Collaboration\Event\CollaborationRequestRejectedEvent;
use App\Domain\Collaboration\Exception\AlreadyExistCollaborationRequestException;
use App\Domain\Collaboration\Exception\InvalidRoleRequestException;
use App\Domain\Collaboration\Repository\CollaborationRequestRepository;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class CollaborationRequestService
{

    public function __construct(
        private readonly CollaborationRequestRepository $collaborationRequestRepository,
        private readonly RoleHierarchyInterface $roleHierarchy,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository $userRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function createCollaborationRequest( CollaborationRequest $collaborationRequest ) : void
    {
        $requester = $collaborationRequest->getRequester();
        $roleRequested = $collaborationRequest->getRoleRequested();
        if ($this->hasRoleOrHigher($requester, $roleRequested)) {
            throw new InvalidRoleRequestException("Vous avez déjà ce rôle ou un rôle supérieur.");
        }

        $existingRequest = $this->collaborationRequestRepository->findOneBy([
            'requester' => $requester,
            'roleRequested' => $roleRequested,
            'status' => CollaborationRequestStatus::PENDING
        ]);

        if ( $existingRequest ) {
            throw new AlreadyExistCollaborationRequestException();
        }

        $collaborationRequest->updateTimestamps();
        $this->collaborationRequestRepository->save( $collaborationRequest, true );

        $this->dispatcher->dispatch( new CollaborationRequestCreatedEvent($collaborationRequest) );
    }

    private function hasRoleOrHigher(User $user, CollaborationRequestRole $roleRequested): bool
    {
        $userRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        return in_array($roleRequested->value, $userRoles, true);
    }

    public function accept( CollaborationRequest $collaborationRequest ) : void
    {
        $collaborationRequest->setStatus( CollaborationRequestStatus::ACCEPTED );
        $this->collaborationRequestRepository->save( $collaborationRequest, true );

        // Add role to user
        $requester = $collaborationRequest->getRequester();
        $roles = $requester->getRoles();

        $roles[] = $collaborationRequest->getRoleRequested()->value;
        $requester->setRoles( array_unique($roles) );

        $this->userRepository->save( $requester, true );

        // Dispatch event
        $this->dispatcher->dispatch( new CollaborationRequestAcceptedEvent($collaborationRequest) );
    }

    public function reject( CollaborationRequest $collaborationRequest ) : void
    {
        $collaborationRequest->setStatus( CollaborationRequestStatus::REJECTED );
        $this->collaborationRequestRepository->save( $collaborationRequest, true );

        // Dispatch event
        $this->dispatcher->dispatch( new CollaborationRequestRejectedEvent($collaborationRequest) );
    }

}