<?php

namespace App\Infrastructure\Social\Authenticator;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class GoogleAuthenticator extends AbstractOAuthAuthenticator
{

    protected string $serviceName = 'google';

    protected function getUserFromResourceOwner( ResourceOwnerInterface $resourceOwner, UserRepository $userRepository ): ?User
    {
        if (!($resourceOwner instanceof GoogleUser)) {
            throw new UnsupportedUserException("Expecting GoogleUser");
        }

        if ( true !== ($resourceOwner->toArray()['email_verified'] ?? null) ) {
            throw new AuthenticationException('Google account is not verified');
        }

        $user = $userRepository->findForOauth('google', $resourceOwner->getId(), $resourceOwner->getEmail());
        if ($user && (null === $user->getGoogleId() || null === $user->getGoogleEmail())) {
            $user->setGoogleId($resourceOwner->getId());
            $user->setGoogleEmail($resourceOwner->getEmail());
            $userRepository->save($user, true);
        }

        return $user;
    }

}
