<?php

namespace App\Http\Controller\Auth;

use App\Http\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route( path: '/connexion', name: 'login' )]
    public function login( AuthenticationUtils $authenticationUtils ) : Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_home');
        }
//        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
//            $this->addFlash('info', );
//        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render( 'pages/auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error] );
    }

    #[Route( path: '/deconnexion', name: 'logout' )]
    public function logout() : void
    {
        throw new \LogicException( 'This method can be blank - it will be intercepted by the logout key on your firewall.' );
    }
}
