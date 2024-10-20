<?php

namespace App\Domain\Auth\Core\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Exception\TooManyEmailChangeException;
use App\Domain\Auth\Event\RequestEmailChangeEvent;
use App\Domain\Auth\Registration\Entity\EmailVerification;
use App\Domain\Auth\Registration\Repository\EmailVerificationRepository;
use App\Infrastructure\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EmailChangeService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly EmailVerificationRepository $emailVerificationRepository,
        private readonly TokenGeneratorService       $tokenGeneratorService,
        private readonly EventDispatcherInterface    $eventDispatcher
    )
    {
    }

    /**
     * Request an email change for the user
     *
     * @param User $user
     * @param string $newEmail
     * @return void
     *
     * @throws TooManyEmailChangeException
     */
    public function requestEmailChange( User $user, string $newEmail ) : void
    {
        // Vérifie si une demande de vérification pour cet email existe déjà
        $emailVerificationByEmail = $this->emailVerificationRepository->findEmailVerificationByEmail( $newEmail );
        if ( $emailVerificationByEmail && ( !$emailVerificationByEmail->isExpired() ) && ( $emailVerificationByEmail->getAuthor() !== $user ) ) {
            throw new \LogicException( 'Cet email est déjà utilisé' );
        }

        $latestEmailVerification = $this->emailVerificationRepository->findEmailVerification( $user );
        if ( $latestEmailVerification && ( !$latestEmailVerification->isExpired() ) ) {
            throw new TooManyEmailChangeException( $latestEmailVerification );
        } else {
            if ( $latestEmailVerification ) {
                $this->entityManager->remove( $latestEmailVerification );
            }
        }

        $emailVerification = ( new EmailVerification() )
            ->setEmail( $newEmail )
            ->setAuthor( $user )
            ->setCreatedAt( new \DateTimeImmutable() )
            ->setToken( $this->tokenGeneratorService->generate() );

        $this->entityManager->persist( $emailVerification );
        $this->entityManager->flush();

        // Déclenche l'événement de demande de changement d'email
        $this->eventDispatcher->dispatch( new RequestEmailChangeEvent( $emailVerification ) );
    }

    /**
     * Confirm the email change
     *
     * @param EmailVerification $emailVerification
     * @return void
     */
    public function confirmEmailChange( EmailVerification $emailVerification ) : void
    {
        $emailVerification->getAuthor()->setEmail( $emailVerification->getEmail() );
        $this->entityManager->persist( $emailVerification->getAuthor() );
        $this->entityManager->flush();

        $this->entityManager->remove( $emailVerification );
        $this->entityManager->flush();
    }

    /**
     * Get the latest valid email verification for the user
     *
     * @param User $user
     * @return EmailVerification|null
     */
    public function getLatestValidEmailVerification( User $user ) : ?EmailVerification
    {
        return $this->emailVerificationRepository->findEmailVerification( $user );
    }

}