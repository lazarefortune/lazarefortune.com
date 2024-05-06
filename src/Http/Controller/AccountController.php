<?php

namespace App\Http\Controller;

use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Password\Form\UpdatePasswordForm;
use App\Domain\Profile\Dto\ProfileUpdateData;
use App\Domain\Profile\Exception\TooManyEmailChangeException;
use App\Domain\Profile\Form\DeleteAccountForm;
use App\Domain\Profile\Form\UserUpdateForm;
use App\Domain\Profile\Service\DeleteAccountService;
use App\Domain\Profile\Service\ProfileService;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/mon-compte' )]
#[IsGranted( 'ROLE_USER' )]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly ProfileService              $profileService,
        private readonly DeleteAccountService        $deleteAccountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AppointmentService          $appointmentService
    )
    {
    }

    #[Route( '/', name: 'profile' )]
    #[isGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function index( Request $request ) : Response
    {
        [$formProfile, $response] = $this->createFormProfile( $request );

        $user = $this->getUserOrThrow();

        if ( $response ) {
            return $response;
        }

        [$formPassword, $response] = $this->createFormPassword( $request );

        if ( $response ) {
            return $response;
        }

        [$formDeleteAccount, $response] = $this->createFormDeleteAccount( $request );

        if ( $response ) {
            return $response;
        }

        // Check if user request email change
        $requestEmailChange = $this->profileService->getLatestValidEmailVerification( $user );

        // get user's appointments
        $appointments = $this->appointmentService->getUserAppointments( $user );

        return $this->render( 'account/index.html.twig', [
            'formProfile' => $formProfile->createView(),
            'formPassword' => $formPassword->createView(),
            'formDeleteAccount' => $formDeleteAccount->createView(),
            'requestEmailChange' => $requestEmailChange,
            'appointments' => $appointments,
            'invoices' => array()
        ] );
    }

    private function createFormProfile( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( UserUpdateForm::class, new ProfileUpdateData( $user ) );

        $form->handleRequest( $request );
        try {
            if ( $form->isSubmitted() && $form->isValid() ) {
                $data = $form->getData();
//                dd($data);
                $this->profileService->updateProfile( $data );

                if ( $data->email !== $user->getEmail() ) {
                    $this->addFlash( 'success', 'Vous allez recevoir un email pour confirmer votre nouvelle adresse email' );
                    return [$form, $this->redirectToRoute( 'app_profile' )];
                } else {
                    $this->addFlash( 'success', 'Informations mises à jour avec succès' );
                }

                return [$form, $this->redirectToRoute( 'app_profile' )];
            }
        } catch ( TooManyEmailChangeException ) {
            $this->addFlash( 'danger', 'Vous avez déjà demandé un changement d\'email, veuillez patienter avant de pouvoir en faire un nouveau' );
        }

        return [$form, null];
    }

    private function createFormPassword( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( UpdatePasswordForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            $this->profileService->updatePassword( $user, $form->get( 'password' )->getData() );

            $this->addFlash( 'success', 'Mot de passe mis à jour avec succès' );
            return [$form, $this->redirectToRoute( 'app_profile' )];
        }

        return [$form, null];
    }

    private function createFormDeleteAccount( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( DeleteAccountForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {

            $data = $form->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['password'] ) ) {
                $this->addFlash( 'error', 'Impossible de supprimer votre compte, mot de passe invalide' );
                return [$form, $this->redirectToRoute( 'app_profile' )];
            }

            try {
                $this->deleteAccountService->deleteAccountRequest( $user, $request );
            } catch ( \LogicException $e ) {
                $this->addFlash( 'error', $e->getMessage() );
                return [$form, $this->redirectToRoute( 'app_profile' )];
            }

            $this->addFlash( 'info', 'Votre demande de suppression de compte a bien été prise en compte' );
            return [$form, $this->redirectToRoute( 'app_profile' )];
        }

        return [$form, null];
    }


    #[Route( '/annuler-suppression-compte', name: 'cancel_account_deletion' )]
    public function cancelAccountDeletion( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $this->deleteAccountService->cancelAccountDeletionRequest( $user );
        $this->addFlash( 'success', 'Votre demande de suppression de compte a bien été annulée' );
        return $this->redirectToRoute( 'app_profile' );
    }
}
