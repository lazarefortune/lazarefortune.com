<?php

namespace App\Domain\Client\Service;

use App\Domain\Auth\Dto\NewUserData;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\EmailConfirmationCompletedEvent;
use App\Domain\Auth\Event\EmailConfirmationRequestedEvent;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class AuthService
{
    public function __construct(
        private readonly VerifyEmailHelperInterface  $verifyEmailHelper,
        private readonly EntityManagerInterface      $entityManager,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function registerNewUser( NewUserData $newUserData ) : User
    {
        $newUser = $newUserData->user
            ->setEmail( $newUserData->email )
            ->setFullname( $newUserData->fullname )
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $newUserData->user,
                    $newUserData->plainPassword
                )
            )
            ->setRoles( ['ROLE_CLIENT'] )
            ->setCgu( true );

        $this->entityManager->persist( $newUser );
        $this->entityManager->flush();

        $userRegistrationCompletedEvent = new UserRegistrationCompletedEvent( $newUser );
        $this->eventDispatcher->dispatch( $userRegistrationCompletedEvent, UserRegistrationCompletedEvent::NAME );

        return $newUser;
    }

    /**
     * Validate the email confirmation link, and activate the user account
     * @param User $user
     * @param string $uri
     * @throws VerifyEmailExceptionInterface
     */
    public function confirmAccount( User $user, $uri ) : void
    {
        $this->verifyEmailHelper->validateEmailConfirmation( $uri, $user->getId(), $user->getEmail() );

        $emailConfirmationCompletedEvent = new EmailConfirmationCompletedEvent( $user );
        $this->eventDispatcher->dispatch( $emailConfirmationCompletedEvent, EmailConfirmationCompletedEvent::NAME );

        $user->setIsVerified( true );
        $this->entityManager->flush();
    }

    /**
     * Send an email to the user to confirm his account
     * @param User $user
     * @return void
     */
    public function sendAccountConfirmationEmail( User $user ) : void
    {
        $emailConfirmationRequestedEvent = new EmailConfirmationRequestedEvent( $user );
        $this->eventDispatcher->dispatch( $emailConfirmationRequestedEvent, EmailConfirmationRequestedEvent::NAME );
    }

    public function logout( Request $request = null ) : void
    {
        $request = $request ?: new Request();
        $request->getSession()->invalidate();
    }
}