<?php

namespace App\Http\Controller\Social;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Http\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route(path: '/oauth/unlink/{service}', name: 'oauth_unlink')]
    #[IsGranted('ROLE_USER')]
    public function disconnect(string $service, UserRepository $userRepository): RedirectResponse
    {
        $this->ensureServiceAccepted($service);
        $method = 'set'.ucfirst($service).'Id';
        $methodEmail = 'set'.ucfirst($service).'Email';
        /** @var User $user */
        $user = $this->getUser();
        $user->$method(null);
        $user->$methodEmail(null);
        $userRepository->save($user, true);
        $this->addFlash('success', 'Votre compte a bien été dissocié de '.$service);

        return $this->redirectToRoute('app_account_security');
    }

    #[Route(path: '/oauth/check/{service}', name: 'oauth_check')]
    public function check(
        string $service,
        UserRepository $userRepository
    ): Response
    {
        #return new Response();

        // Vérifier que le service est géré (google, github, etc.)
        $this->ensureServiceAccepted($service);

        // Récupérer le client OAuth correspondant (Google, GitHub...)
        $client = $this->clientRegistry->getClient($service);

        // Récupérer l'utilisateur "OAuth" (Resource Owner)
        // ex: GoogleUser ou GithubResourceOwner
        $oauthUser = $client->fetchUser();

        // Récupérer son email (peut être null si l'utilisateur le masque)
        $email = $oauthUser->getEmail() ?? null;

        // On suppose que l'utilisateur est déjà connecté
        // (sinon on fait un authenticator dédié).
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            // si non connecté, on pourrait rediriger vers login
            return $this->redirectToRoute('app_login');
        }

        // Stocker l'email "social" si le setter existe
        // Par ex. "google" => setGoogleEmail($email)
        $methodEmail = 'set'.ucfirst($service).'Email';
        if ($email && method_exists($user, $methodEmail)) {
            $user->$methodEmail($email);
        }

        // Optionnel : on peut aussi s'assurer qu'on stocke l'ID social
        // s'il n'est pas déjà mis par l'Authenticator
        // (dépend de ta configuration)
        // $methodId = 'set'.ucfirst($service).'Id';
        // if (method_exists($user, $methodId)) {
        //     // ex: $user->setGoogleId($oauthUser->getId());
        // }

        // Sauvegarde en BDD
        $userRepository->save($user, true);

        // Redirection finale (ex: "Sécurité & Connexion" ou "Mon compte")
        $this->addFlash('success', "Le compte $service a été lié, email récupéré : $email");
        return $this->redirectToRoute('app_account_security');
    }
}