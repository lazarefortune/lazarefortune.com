<?php

namespace App\Domain\Auth\Core\Service;

use App\Domain\Auth\Core\Dto\AvatarDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Password\Event\PasswordUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
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

    public function updateAvatar( AvatarDto $data ) : void
    {
        if (false === $data->file->getRealPath()) {
            throw new \RuntimeException('Impossible de redimensionner un avatar non existant');
        }
        // On redimensionne l'image
//        $manager = new ImageManager(['driver' => 'imagick']);
//        $manager->make($data->file)->fit(110, 110)->save($data->file->getRealPath());

        // On la déplace dans le profil utilisateur
        $data->user->setAvatarFile($data->file);
        $data->user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist( $data->user );
        $this->entityManager->flush();
    }
}
