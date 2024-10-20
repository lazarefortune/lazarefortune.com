<?php

namespace App\Domain\Auth\Core\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Password\Event\PasswordUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Update the profile of the user
     *
     * @param User $user
     * @return void
     */
    public function updateProfile(User $user): void
    {
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Update the password of the user
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    public function updatePassword( User $user, string $password ) : void
    {
        $user->setPassword( $this->passwordHasher->hashPassword( $user, $password ) );
        $this->entityManager->persist( $user );
        $this->entityManager->flush();

        $passwordUpdatedEvent = new PasswordUpdatedEvent( $user );
        $this->eventDispatcher->dispatch( $passwordUpdatedEvent, PasswordUpdatedEvent::NAME );
    }

}
