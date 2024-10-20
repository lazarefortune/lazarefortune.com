<?php

namespace App\Domain\Auth\Password\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Password\Entity\PasswordReset;
use App\Domain\Auth\Password\Event\PasswordResetRequestedEvent;
use App\Domain\Auth\Password\Event\PasswordUpdatedEvent;
use App\Domain\Auth\Password\Exception\TooManyPasswordResetRequestException;
use App\Domain\Auth\Password\Repository\PasswordResetRepository;
use App\Infrastructure\Mailing\MailService;
use App\Infrastructure\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PasswordService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserRepository              $userRepository,
        private readonly TokenGeneratorService       $tokenGeneratorService,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly PasswordResetRepository     $passwordResetRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly MailService           $mailService,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    /**
     * Create a password reset request and dispatch an event to send an email
     * @param string $email
     * @return void
     * @throws TooManyPasswordResetRequestException
     */
    public function requestPasswordReset( string $email ) : void
    {
        $user = $this->entityManager->getRepository( User::class )->findOneBy( ['email' => $email] );

        if ( $user ) {
            $latestPasswordResetRequest = $this->passwordResetRepository->findLatestValidPasswordReset( $user );
            if ( $latestPasswordResetRequest && ( !$latestPasswordResetRequest->isExpired() ) ) {
                throw new TooManyPasswordResetRequestException( $latestPasswordResetRequest );
            } else {
                if ( $latestPasswordResetRequest ) {
                    $this->entityManager->remove( $latestPasswordResetRequest );
                }
            }

            $passwordResetRequest = ( new PasswordReset() )
                ->setAuthor( $user )
                ->setToken( $this->tokenGeneratorService->generate() )
                ->setCreatedAt( new \DateTimeImmutable() );

            $this->entityManager->persist( $passwordResetRequest );
            $this->entityManager->flush();

            $passwordResetRequestedEvent = new PasswordResetRequestedEvent( $passwordResetRequest );
            $this->eventDispatcher->dispatch( $passwordResetRequestedEvent, PasswordResetRequestedEvent::NAME );
        }
    }

    /**
     * Get the user associated with a password reset token
     * @param string $token
     * @return User|null
     */
    public function findUserByResetToken( string $token ) : ?User
    {
        /** @var PasswordReset $passwordReset */
        $passwordReset = $this->passwordResetRepository->findOneBy( ['token' => $token] );
        if ( $passwordReset && !$passwordReset->isExpired() ) {
            return $passwordReset->getAuthor();
        }
        return null;
    }

    /**
     * Reset the password of a user
     * @param string $userId
     * @param string $newPassword
     * @return void
     */
    public function updatePassword( string $userId, string $newPassword ) : void
    {
        $user = $this->userRepository->find( $userId );

        $user->setPassword( $this->userPasswordHasher->hashPassword( $user, $newPassword ) );
        $this->entityManager->flush();

        // remove password reset request
        $passwordReset = $this->passwordResetRepository->findOneBy( ['author' => $user] );
        if ( $passwordReset ) {
            $this->entityManager->remove( $passwordReset );
            $this->entityManager->flush();
        }

        $passwordUpdatedEvent = new PasswordUpdatedEvent( $user );
        $this->eventDispatcher->dispatch( $passwordUpdatedEvent , PasswordUpdatedEvent::NAME );
    }

    /**
     * Send reset password email instruction
     *
     * @param PasswordReset $passwordReset
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendPasswordResetRequestedEmail( PasswordReset $passwordReset ) : void
    {
        $user = $passwordReset->getAuthor();
        $token = $passwordReset->getToken();

        $data = [
            'user' => $user,
            'resetUrl' => $this->urlGenerator->generate( 'app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL )
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Réinitialisation de votre mot de passe',
            'mails/auth/password/password-reset-requested.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    /**
     * Send password updated email information
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendPasswordUpdatedEmail( User $user ) : void
    {
        $email = $this->mailService->createEmail( 'mails/auth/password/password-updated.twig', [
            'user' => $user,
            'loginUrl' => $this->urlGenerator->generate( 'app_login', [], UrlGeneratorInterface::ABSOLUTE_URL ),
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre mot de passe a été modifié' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }
}
