<?php

namespace App\Http\Controller\Auth;

use App\Domain\Auth\Core\Dto\CreateUserDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Login\Service\LoginService;
use App\Domain\Auth\Registration\Form\RegistrationForm;
use App\Domain\Auth\Registration\Service\RegistrationService;
use App\Http\Controller\AbstractController;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    public function __construct(
        private readonly RegistrationService        $registrationService,
        private readonly LoginService               $loginService,
    )
    {
    }

    #[Route( '/inscription', name: 'register' )]
    public function registerUser( Request $request ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }
        
        $form = $this->createForm( RegistrationForm::class, new CreateUserDto( new User() ) );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            try {
                $user = $this->registrationService->createUser( $form->getData() );
            } catch ( Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_register' );
            }

            try {
                return $this->loginService->authenticateUser( $user, $request );
            } catch ( Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_login' );
            }
        }

        return $this->render( 'pages/auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ] );
    }

    #[Route( '/email/validation', name: 'verify_email' )]
    public function validateEmail( Request $request ) : Response
    {
        $userId = $request->get( 'id' );
        $uri = $request->getUri();

        try {
            $this->registrationService->validateUser( $userId , $uri );
        } catch ( Exception $e ) {
            $this->addFlash( 'danger', $e->getMessage() );
            return $this->redirectToRoute( 'app_register' );
        } catch ( VerifyEmailExceptionInterface $e ) {
            $this->addFlash( 'danger', $e->getReason() );
            return $this->redirectToRoute( 'app_register' );
        }

        $this->addFlash('info', 'Votre compte a été activé avec succès');
        return $this->redirectToRoute('app_login');
    }

}

