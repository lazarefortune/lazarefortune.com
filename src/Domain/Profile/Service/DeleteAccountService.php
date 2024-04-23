<?php

namespace App\Domain\Profile\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Client\Service\AuthService;
use App\Domain\Profile\Event\UserDeleteRequestEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAccountService
{
    public function __construct(
        private readonly AuthService              $authService,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface   $em,
    )
    {
    }

    public function deleteAccount( User $user ) : void
    {
        $this->em->remove( $user );
        $this->em->flush();
    }

    public function deleteAccountRequest( User $user, Request $request ) : void
    {
        $this->ensureAccountCanBeDeleted( $user );

        $this->authService->logout( $request );
        $this->dispatcher->dispatch( new UserDeleteRequestEvent( $user ) );

        $user->setDeletedAt( new \DateTimeImmutable( sprintf( '+%d days', User::DAYS_BEFORE_DELETION ) ) );
        $this->em->flush();
    }

    /**
     * Ensure the user account can be deleted.
     *
     * @param User $user
     * @throws \LogicException If the account deletion is not allowed.
     */
    protected function ensureAccountCanBeDeleted( User $user ) : void
    {
        if ( null !== $user->getDeletedAt() ) {
            throw new \LogicException( sprintf( 'La suppression de ce compte est déjà programmée pour le %s.', $user->getDeletedAt()->format( 'd/m/Y' ) ) );
        }

        $unavailableRolesForDeletion = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        if ( array_intersect( $unavailableRolesForDeletion, $user->getRoles() ) ) {
            throw new \LogicException( 'Impossible de supprimer ce compte car vous avez un rôle interdisant la suppression.' );
        }
    }


    public function cancelAccountDeletionRequest( User $user ) : void
    {
        $user->setDeletedAt( null );
        $this->em->flush();
    }
}