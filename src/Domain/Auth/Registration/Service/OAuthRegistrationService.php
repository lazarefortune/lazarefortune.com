<?php

namespace App\Domain\Auth\Registration\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Registration\Event\UserCreatedEvent;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class OAuthRegistrationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ){}


    public function persist(ResourceOwnerInterface $resourceOwner): User
    {
        $user = match (true) {
            $resourceOwner instanceof GoogleUser => (new User())
                ->setEmail($resourceOwner->getEmail())
                ->setCgu(true)
                ->setRoles(['ROLE_USER'])
                ->setFullname($resourceOwner->getFirstName())
                ->setPassword(''),
        };

        $this->userRepository->save($user, true);
        // dispatch event
        $this->eventDispatcher->dispatch(new UserCreatedEvent( $user ), UserCreatedEvent::NAME);
        return $user;
    }
}