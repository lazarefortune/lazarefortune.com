<?php

namespace App\Domain\Auth\Core\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Event\Delete\UserDeleteRequestEvent;
use App\Domain\Auth\Login\Service\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAccountService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface   $em,
        private readonly LoginService             $loginService,
    )
    {
    }

    public function deleteAccount( User $user ) : void
    {
       # $this->dispatcher->dispatch( new UserRequestDeleteSuccessEvent( $user ) );

        $this->em->remove( $user );
        $this->em->flush();
    }

    public function requestAccountDeletion( User $user, Request $request ) : void
    {
        $this->ensureAccountCanBeDeleted( $user );

        $this->loginService->logout( $request );

        $user->setDeletedAt( new \DateTimeImmutable( sprintf( '+%d days', User::DAYS_BEFORE_DELETION ) ) );
        $this->em->flush();

        $this->dispatcher->dispatch( new UserDeleteRequestEvent( $user ) );
    }

    /**
     * Ensure the user account can be deleted.
     *
     * @param User $user
     * @throws \LogicException If the account deletion is not allowed.
     */
    protected function ensureAccountCanBeDeleted( User $user ) : void
    {
//        if ( null !== $user->getDeletedAt() ) {
//            throw new \LogicException( sprintf( 'La suppression de ce compte est déjà programmée pour le %s.', $user->getDeletedAt()->format( 'd/m/Y' ) ) );
//        }
//
//        $unavailableRolesForDeletion = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
//        if ( array_intersect( $unavailableRolesForDeletion, $user->getRoles() ) ) {
//            throw new \LogicException( 'Impossible de supprimer ce compte car vous avez un rôle interdisant la suppression.' );
//        }
    }


    public function cancelAccountDeletion( User $user ) : void
    {
        $user->setDeletedAt( null );
        $this->em->flush();
    }
}