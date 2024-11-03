<?php

namespace App\Infrastructure\Social\Authenticator;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Registration\Service\OAuthRegistrationService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class AbstractOAuthAuthenticator extends OAuth2Authenticator
{

    use TargetPathTrait;

    protected string $serviceName = '';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly OAuthRegistrationService $OAuthRegistrationService
    ){}

    public function supports( Request $request ) : ?bool
    {
        return 'app_oauth_check' === $request->attributes->get('_route') &&
            $request->get('service') === $this->serviceName;
    }

    public function authenticate( Request $request ) : Passport
    {
        $credentials = $this->fetchAccessToken($this->getClient());
        $resourceOwner = $this->getResourceOwnerFromCredentials( $credentials );
        $user = $this->getUserFromResourceOwner( $resourceOwner, $this->userRepository );

        if (null === $user) {
            // crée un compte
            $user = $this->OAuthRegistrationService->persist( $resourceOwner );
        }

        return new SelfValidatingPassport(
            userBadge: new UserBadge($user->getEmail(), fn () => $user),
            badges: [
                new RememberMeBadge()
            ]
        );
    }

    abstract protected function getUserFromResourceOwner( ResourceOwnerInterface $resourceOwner , UserRepository $userRepository ): ?User;

    protected function getResourceOwnerFromCredentials( AccessToken $credentials ): ResourceOwnerInterface
    {
        return $this->getClient()->fetchUserFromToken( $credentials );
    }

    public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $firewallName ) : ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure( Request $request, AuthenticationException $exception ) : ?Response
    {
        if ( $request->hasSession() )
        {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient($this->serviceName);
    }
}