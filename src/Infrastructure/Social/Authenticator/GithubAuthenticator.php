<?php

namespace App\Infrastructure\Social\Authenticator;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GithubAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'github';

    public function getUserFromResourceOwner( ResourceOwnerInterface $resourceOwner, UserRepository $userRepository): ?User
    {
        if (!($resourceOwner instanceof GithubResourceOwner)) {
            throw new \RuntimeException('Expecting GithubResourceOwner as the first parameter');
        }
        $user = $userRepository->findForOauth('github', $resourceOwner->getId(), $resourceOwner->getEmail());
        if ($user && null === $user->getGithubId()) {
            $user->setGithubId($resourceOwner->getId());
            $userRepository->save($user, true);
        }

        return $user;
    }

    public function getResourceOwnerFromCredentials(AccessToken $credentials): GithubResourceOwner
    {
        /** @var GithubResourceOwner $githubUser */
        $githubUser = parent::getResourceOwnerFromCredentials($credentials);
        $response = HttpClient::create()->request(
            'GET',
            'https://api.github.com/user/emails',
            [
                'headers' => [
                    'authorization' => "token {$credentials->getToken()}",
                ],
            ]
        );
        $emails = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        foreach ($emails as $email) {
            if (true === $email['primary'] && true === $email['verified']) {
                $data = $githubUser->toArray();
                $data['email'] = $email['email'];

                return new GithubResourceOwner($data);
            }
        }

//        throw new NotVerifiedEmailException();
        throw new AccessDeniedException();
    }
}