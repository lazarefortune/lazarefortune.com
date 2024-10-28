<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Dto\AvatarDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Exception\TooManyEmailChangeException;
use App\Domain\Auth\Core\Form\DeleteAccountForm;
use App\Domain\Auth\Core\Form\EmailUpdateForm;
use App\Domain\Auth\Core\Form\ProfileUpdateForm;
use App\Domain\Auth\Core\Form\UpdatePasswordForm;
use App\Domain\Auth\Core\Service\AccountService;
use App\Domain\Auth\Core\Service\DeleteAccountService;
use App\Domain\Auth\Core\Service\EmailChangeService;
use App\Domain\History\Service\HistoryService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/mon-compte', name: 'account_' )]
#[IsGranted( 'ROLE_USER' )]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService              $accountService,
        private readonly EmailChangeService          $emailChangeService,
        private readonly DeleteAccountService        $deleteAccountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly HistoryService              $historyService,
    )
    {
    }

    #[Route( '/', name: 'profile' )]
    #[isGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function index( Request $request ) : Response
    {
        // Profile update form
        $user = $this->getUserOrThrow();

        $formProfile = $this->createForm( ProfileUpdateForm::class , $user );
        $formProfile->handleRequest( $request );
        if ( $formProfile->isSubmitted() && $formProfile->isValid() ) {
            $data = $formProfile->getData();
            $this->accountService->updateProfile( $data );
            $this->addFlash( 'success', 'Informations mises à jour avec succès' );
        }

        // Email update form
        $formEmail = $this->createForm( EmailUpdateForm::class );
        $formEmail->handleRequest( $request );
        if ($formEmail->isSubmitted() && $formEmail->isValid()) {
            $data = $formEmail->getData();
            $newEmail = $data['email'];
            try {
                $this->emailChangeService->requestEmailChange($user, $newEmail);
                $this->addFlash('success', 'Vous allez recevoir un email pour confirmer votre nouvelle adresse email');
            } catch (\LogicException $e) {
                $formEmail->get('email')->addError(new FormError($e->getMessage()));
            } catch (TooManyEmailChangeException) {
                $this->addFlash('danger', 'Vous avez déjà demandé un changement d\'email, veuillez patienter avant de pouvoir en faire un nouveau');
            }
        }

        // Password update form
        $formPassword = $this->createForm( UpdatePasswordForm::class );
        $formPassword->handleRequest( $request );
        if ( $formPassword->isSubmitted() && $formPassword->isValid() ) {
            // Verify current password
            $data = $formPassword->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['currentPassword'] ) ) {
                $formPassword->get( 'currentPassword' )->addError( new FormError( 'Mot de passe actuel invalide' ) );
            } else {
                $this->accountService->updatePassword( $user, $data['newPassword'] );
                $this->addFlash( 'success', 'Mot de passe mis à jour avec succès' );
            }
        }

        // Delete account form
        $formDeleteAccount = $this->createForm( DeleteAccountForm::class );
        $formDeleteAccount->handleRequest( $request );
        if ( $formDeleteAccount->isSubmitted() && $formDeleteAccount->isValid() ) {
            $data = $formDeleteAccount->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['password'] ) ) {
                $this->addFlash( 'error', 'Impossible de supprimer votre compte, mot de passe invalide' );
                return $this->redirectToRoute( 'app_account_profile' );
            }

            try {
                $this->deleteAccountService->requestAccountDeletion( $user, $request );
            } catch ( \LogicException $e ) {
                $this->addFlash( 'error', $e->getMessage() );
                return $this->redirectToRoute( 'app_account_profile' );
            }

            $this->addFlash( 'info', 'Votre demande de suppression de compte a bien été prise en compte' );

            return $this->redirectToRoute( 'app_account_profile' );
        }

        // latest email change request for the user
        $requestEmailChange = $this->emailChangeService->getLatestValidEmailVerification( $user );

        $watchlist = $this->historyService->getLastWatchedContent($user);

        return $this->render( 'pages/public/account/index.html.twig', [
            'watchlist' => $watchlist,
            'formProfile' => $formProfile->createView(),
            'formEmail'   => $formEmail->createView(),
            'formPassword' => $formPassword->createView(),
            'formDeleteAccount' => $formDeleteAccount->createView(),
            'requestEmailChange' => $requestEmailChange,
        ] );
    }

    #[Route('/avatar', name: 'avatar' , methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateAvatar(
        Request $request,
        ValidatorInterface $validator,
        AccountService $accountService,
    ) : Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = new AvatarDto($request->files->get('avatar'), $user);
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            $this->addFlash('error', (string) $errors->get(0)->getMessage());
        } else {
            $accountService->updateAvatar($data);
            $this->addFlash('success', 'Avatar mis à jour avec succès');
        }

        return $this->redirectToRoute('app_account_profile');
    }
}
