<?php

namespace App\Domain\Auth\Core\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UrlMatcherInterface $urlMatcher
    )
    {
    }

    public function authenticate( Request $request ) : Passport
    {
        $email = $request->request->get( 'email', '' );

        $request->getSession()->set( Security::LAST_USERNAME, $email );

        return new Passport(
            new UserBadge( $email ),
            new PasswordCredentials( $request->request->get( 'password', '' ) ),
            [
                new CsrfTokenBadge( 'authenticate', $request->request->get( '_csrf_token' ) ),
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $firewallName ) : ?Response
    {
        $redirect = $this->validateRedirect($request, $this->urlMatcher);
        if ($redirect) {
            return new RedirectResponse($redirect);
        }

        if ( $targetPath = $this->getTargetPath( $request->getSession(), $firewallName ) ) {
            return new RedirectResponse( $targetPath );
        }

        return new RedirectResponse( $this->urlGenerator->generate( 'app_home' ) );
    }

    protected function getLoginUrl( Request $request ) : string
    {
        return $this->urlGenerator->generate( self::LOGIN_ROUTE );
    }

    /**
     * Valide l'URL de redirection fournie dans la requête.
     *
     * Cette fonction vérifie que l'URL est interne (c'est-à-dire que le host correspond),
     * qu'elle correspond à une route existante, et qu'elle utilise une méthode autorisée (par défaut GET).
     *
     * @param Request               $request     La requête en cours.
     * @param UrlMatcherInterface   $urlMatcher  Le matcher de route.
     * @param array                 $allowedMethods  Les méthodes HTTP autorisées (par défaut ['GET']).
     * @return string|null  L'URL de redirection valide ou null si aucune correspondance n'est trouvée.
     */
    private function validateRedirect(
        Request $request,
        UrlMatcherInterface $urlMatcher,
        array $allowedMethods = ['GET']
    ): ?string {
        // Récupération de l'URL de redirection
        $redirect = $request->get('redirect');
        if (!$redirect) {
            return null;
        }

        // Parse de l'URL (pour extraire le host, le path, etc.)
        $parsedUrl = parse_url($redirect);

        // Vérifier si l'URL est externe : on peut rejeter les URL dont le host ne correspond pas à celui de notre application.
        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $request->getHost()) {
            return null;
        }

        // Extraction du chemin (obligatoire pour matcher une route)
        $path = $parsedUrl['path'] ?? '/';

        // Sauvegarde de la méthode HTTP originale dans le contexte du matcher
        $originalMethod = $urlMatcher->getContext()->getMethod();

        // Pour chaque méthode autorisée, essayer de trouver une route correspondante.
        foreach ($allowedMethods as $method) {
            $urlMatcher->getContext()->setMethod($method);
            try {
                // Si le chemin correspond à une route, on considère l'URL comme valide.
                $urlMatcher->match($path);
                // Restauration de la méthode initiale dans le contexte
                $urlMatcher->getContext()->setMethod($originalMethod);
                return $redirect; // L'URL complète (avec query ou fragment) est retournée.
            } catch (\Exception $e) {
                // La route n'est pas trouvée pour cette méthode, on essaie la suivante.
            }
        }

        // Restauration de la méthode originale
        $urlMatcher->getContext()->setMethod($originalMethod);

        // Si aucune méthode ne permet de matcher, on retourne null (ou on peut rediriger vers une URL par défaut).
        return null;
    }

}
