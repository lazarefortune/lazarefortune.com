<?php

namespace App\Http\Controller\Auth;

use App\Domain\Auth\Core\Dto\CreateUserDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Login\Service\LoginService;
use App\Domain\Auth\Registration\Form\RegisterFullname;
use App\Domain\Auth\Registration\Form\RegistrationForm;
use App\Domain\Auth\Registration\Service\RegistrationService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
                $this->loginService->authenticateUser( $user, $request );
                return $this->redirectToRoute( 'app_register_fullname' );
            } catch ( Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_login' );
            }
        }

        return $this->render( 'pages/auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ] );
    }

    #[Route( '/inscription/nom-complet', name: 'register_fullname', methods: ['GET', 'POST'] )]
    #[IsGranted('ROLE_USER')]
    public function registerUserName(Request $request, EntityManagerInterface $em) : Response
    {
        $user = $this->getUserOrThrow();

        $fullname = $request->query->get( 'fullname' );

        $form = $this->createForm( RegisterFullname::class, ['fullname' => $fullname] );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $fullname = $form->get('fullname')->getData();
            $user->setFullname( $fullname );

            $em->persist( $user );
            $em->flush();

            $this->addFlash('success', 'Inscription terminée avec succès !');
            return $this->redirectToRoute( 'app_home' );
        }

        return $this->render( 'pages/auth/register-user-fullname.html.twig' , [
            'form' => $form->createView(),
        ]);
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

