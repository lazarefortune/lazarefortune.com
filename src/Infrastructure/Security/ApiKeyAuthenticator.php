<?php

namespace App\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{

    public function __construct(private readonly ApiKeyUserProvider $userProvider)
    {
    }

    public function supports( Request $request ) : ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), "Bearer");
    }

    public function authenticate( Request $request ) : Passport
    {
        $auth = $request->headers->get('Authorization', "");
        $token = str_replace("Bearer ",  '',$auth);

        return new Passport(
            new UserBadge($token, function($apiKey) {
                return $this->userProvider->loadUserByIdentifier($apiKey);
            }),
            new CustomCredentials(function($credentials, $user) {
                return $user->getApiKey() === $credentials;
            }, $token)
        );
    }

    public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $firewallName ) : ?Response
    {
        return null;
    }

    public function onAuthenticationFailure( Request $request, AuthenticationException $exception ) : ?Response
    {
        return new JsonResponse(['error' => 'Bad credentials'], Response::HTTP_FORBIDDEN);
    }
}