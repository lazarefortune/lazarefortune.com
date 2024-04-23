<?php

namespace App\Domain\Profile\Service;

use App\Domain\Auth\Entity\EmailVerification;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\RequestEmailChangeEvent;
use App\Domain\Auth\Repository\EmailVerificationRepository;
use App\Domain\Profile\Dto\ProfileUpdateData;
use App\Domain\Profile\Event\PasswordChangeSuccessEvent;
use App\Domain\Profile\Exception\TooManyEmailChangeException;
use App\Infrastructure\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly EmailVerificationRepository $emailVerificationRepository,
        private readonly TokenGeneratorService       $tokenGeneratorService,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    /**
     * @throws TooManyEmailChangeException
     */
    public function updateProfile( ProfileUpdateData $data ) : void
    {
        $data->user->setFullname( $data->fullname );
        $data->user->setPhone( $data->phone );
        $data->user->setDateOfBirthday( $data->dateOfBirthday );

        if ( $data->email !== $data->user->getEmail() ) {
            $latestEmailVerification = $this->emailVerificationRepository->findEmailVerification( $data->user );
            if ( $latestEmailVerification && ( !$latestEmailVerification->isExpired() ) ) {
                throw new TooManyEmailChangeException( $latestEmailVerification );
            } else {
                if ( $latestEmailVerification ) {
                    $this->entityManager->remove( $latestEmailVerification );
                }
            }

            // TODO: Check if email is already used in another request email change

            // store new email
            $emailVerification = ( new EmailVerification() )
                ->setEmail( $data->email )
                ->setAuthor( $data->user )
                ->setCreatedAt( new \DateTimeImmutable() )
                ->setToken( $this->tokenGeneratorService->generate() );

            $this->entityManager->persist( $emailVerification );

            // Event de vérification de l'email
            $this->eventDispatcher->dispatch( new RequestEmailChangeEvent( $emailVerification ) );
        }

        $this->entityManager->flush();
    }

    public function updateEmail( EmailVerification $emailVerification ) : void
    {
        $emailVerification->getAuthor()->setEmail( $emailVerification->getEmail() );
        $this->entityManager->remove( $emailVerification );
        $this->entityManager->flush();
    }

    public function getLatestValidEmailVerification( User $user )
    {
        return $this->emailVerificationRepository->findLatestValidEmailVerification( $user );
    }

    public function updatePassword( User $user, string $password ) : void
    {
        $user->setPassword( $this->passwordHasher->hashPassword( $user, $password ) );
        $this->entityManager->persist( $user );
        $this->entityManager->flush();

        $passwordUpdatedEvent = new PasswordChangeSuccessEvent( $user );
        $this->eventDispatcher->dispatch( $passwordUpdatedEvent );
    }
}