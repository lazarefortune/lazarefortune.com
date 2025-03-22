<?php

namespace App\Http\Controller\Auth;

use App\Domain\Auth\Password\Exception\TooManyPasswordResetRequestException;
use App\Domain\Auth\Password\Form\ForgotPasswordForm;
use App\Domain\Auth\Password\Form\ResetPasswordForm;
use App\Domain\Auth\Password\Service\PasswordService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    public function __construct(
        private readonly PasswordService $passwordService
    )
    {
    }

    #[Route( path: '/mot-de-passe-oublie', name: 'forgot_password', methods: ['GET', 'POST'] )]
    public function forgotPassword( Request $request ) : Response
    {
        /*
        if ( $this->getUser() ) {
            return $this->redirectBack( 'app_home' );
        }
        */

        // si dans la requête il y a l'email on pré-rempli le formulaire
        $email = $request->query->get( 'email' );

        $form = $this->createForm( ForgotPasswordForm::class , ['email' => $email] );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $email = $form->get( 'email' )->getData();
            try {
                $this->passwordService->requestPasswordReset( $email );
            } catch ( TooManyPasswordResetRequestException $e ) {
                $this->addFlash( 'warning', 'Trop de demandes de réinitialisation de mot de passe. Veuillez réessayer plus tard' );
            }
            $this->addFlash( 'success', 'Si votre adresse email est valide, vous allez recevoir un email vous permettant de réinitialiser votre mot de passe' );

            $this->redirectBack( 'app_forgot_password' );
        }

        return $this->render( 'pages/auth/forgot_password.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( path: '/modifier-mot-de-passe/{token}', name: 'reset_password', methods: ['GET', 'POST'] )]
    public function resetPassword( Request $request, string $token ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectBack( 'app_home' );
        }

        $user = $this->passwordService->findUserByResetToken( $token );
        if ( !$user ) {
            $this->addFlash( 'danger', 'Token invalide' );
            return $this->redirectToRoute( 'app_forgot_password' );
        }

        $form = $this->createForm( ResetPasswordForm::class );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $newPassword = $form->get( 'password' )->getData();
            $this->passwordService->updatePassword( $user->getId(), $newPassword );

            $this->addFlash( 'success', 'Mot de passe modifié avec succès, vous pouvez vous connecter' );
            return $this->redirectToRoute( 'app_login' );
        }

        return $this->render( 'pages/auth/reset_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ] );
    }
}