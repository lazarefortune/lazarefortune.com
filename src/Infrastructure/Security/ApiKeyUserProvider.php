<?php

namespace App\Infrastructure\Security;

namespace App\Infrastructure\Security;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct( private readonly EntityManagerInterface $em){

    }

    public function loadUserByIdentifier(string $identifier): User
    {
        $user = $this->em->getRepository(User::class)->findBy([
           'apiKey' => $identifier
        ]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('No user found for apiKey "%s".', $identifier));
        }

        return $user[0];
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
