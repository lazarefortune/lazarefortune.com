<?php

namespace App\Http\Controller;

use App\Domain\Auth\Form\ChangePasswordForm;
use App\Domain\Auth\Form\ForgotPasswordForm;
use App\Domain\Auth\Service\PasswordService;
use App\Domain\Profile\Exception\TooManyPasswordResetRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{

    public function __construct(
        private PasswordService $passwordService
    )
    {
    }

    #[Route( path: '/connexion', name: 'login' )]
    public function login( AuthenticationUtils $authenticationUtils ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render( 'auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error] );
    }

    #[Route( path: '/deconnexion', name: 'logout' )]
    public function logout() : void
    {
        throw new \LogicException( 'This method can be blank - it will be intercepted by the logout key on your firewall.' );
    }

    #[Route( path: '/mot-de-passe-oublie', name: 'forgot_password', methods: ['GET', 'POST'] )]
    public function forgotPassword( Request $request ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }

        $form = $this->createForm( ForgotPasswordForm::class );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $email = $form->get( 'email' )->getData();
            try {
                $this->passwordService->forgotPasswordRequest( $email );
            } catch ( TooManyPasswordResetRequestException $e ) {
                $this->addFlash( 'warning', 'Trop de demandes de réinitialisation de mot de passe. Veuillez réessayer plus tard' );
            }
            $this->addFlash( 'success', 'Si votre adresse email est valide, vous allez recevoir un email vous permettant de réinitialiser votre mot de passe' );
            $this->redirectBack( 'app_forgot_password' );
        }

        return $this->render( 'auth/forgot-password.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( path: '/mot-de-passe-oublie/{token}', name: 'reset_password', methods: ['GET', 'POST'] )]
    public function resetPassword( Request $request, string $token ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }

        $user = $this->passwordService->getUserByPasswordResetToken( $token );
        if ( !$user ) {
            $this->addFlash( 'danger', 'Ce lien a expiré ou est invalide, veuillez renouveler votre demande de réinitialisation de mot de passe' );
            return $this->redirectToRoute( 'app_forgot_password' );
        }

        $form = $this->createForm( ChangePasswordForm::class );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $newPassword = $form->get( 'password' )->getData();
            $this->passwordService->resetPassword( $user, $newPassword );

            $this->addFlash( 'success', 'Votre mot de passe a bien été modifié' );
            return $this->render( 'pages/message.html.twig' );
        }

        return $this->render( 'auth/reset-password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ] );
    }
}
