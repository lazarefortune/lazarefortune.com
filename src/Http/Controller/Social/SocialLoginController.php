<?php

namespace App\Http\Controller\Social;

use App\Http\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SocialLoginController extends AbstractController
{
    private const SCOPES = [
        'google' => [],
        'github' => ['user:email'],
    ];

    public function __construct(private readonly ClientRegistry $clientRegistry){}

    #[Route(path: '/oauth/connect/{service}', name: 'oauth_connect')]
    public function connect(string $service): RedirectResponse
    {
        $this->ensureServiceAccepted( $service );

        return $this->clientRegistry->getClient($service)->redirect( self::SCOPES[$service] );
    }

    private function ensureServiceAccepted(string $service): void
    {
        if (!in_array($service, array_keys(self::SCOPES))) {
            throw new AccessDeniedException();
        }
    }


    #[Route(path: '/oauth/check/{service}', name: 'oauth_check')]
    public function check(): Response
    {
        return new Response();
    }
}