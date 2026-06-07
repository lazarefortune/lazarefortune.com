<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function register(User $user, string $plainPassword): User
    {
        if ($this->userRepository->findOneByEmail($user->getEmail()) !== null) {
            throw new \InvalidArgumentException('Un compte existe déjà avec cette adresse e-mail.');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setRoles([]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
