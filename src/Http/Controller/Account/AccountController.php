<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Dto\AvatarDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Event\Unverified\AccountVerificationRequestEvent;
use App\Domain\Auth\Core\Exception\TooManyEmailChangeException;
use App\Domain\Auth\Core\Form\DeleteAccountForm;
use App\Domain\Auth\Core\Form\EmailUpdateForm;
use App\Domain\Auth\Core\Form\ProfileUpdateForm;
use App\Domain\Auth\Core\Form\UpdatePasswordForm;
use App\Domain\Auth\Core\Service\AccountService;
use App\Domain\Auth\Core\Service\DeleteAccountService;
use App\Domain\Auth\Core\Service\EmailChangeService;
use App\Domain\Badge\BadgeService;
use App\Domain\Badge\Entity\Badge;
use App\Domain\Badge\Entity\BadgeAction;
use App\Domain\History\Service\HistoryService;
use App\Domain\Premium\Repository\SubscriptionRepository;
use App\Domain\Premium\Repository\TransactionRepository;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Payment\Stripe\StripeApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        private readonly BadgeService                $badgeService,
        private readonly TransactionRepository       $transactionRepository,
        private readonly EntityManagerInterface      $em,
        private readonly StripeApi                   $api
    )
    {
    }

    #[Route( '/', name: 'index' )]
    #[isGranted( 'ROLE_USER' )]
    public function index( Request $request ) : Response
    {
        return $this->render( 'pages/public/account/index.html.twig');
    }

    #[IsGranted( 'ROLE_USER' )]
    #[Route('/profil', name: 'profile')]
    public function profile( Request $request ) : Response
    {
        $user = $this->getUser();

        $formProfile = $this->createForm( ProfileUpdateForm::class , $user );
        $formProfile->handleRequest( $request );

        if ( $formProfile->isSubmitted() && $formProfile->isValid() ) {
            $data = $formProfile->getData();

            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $formProfile->get('avatarFile')->getData();
            if ($avatarFile) {
                $data->setAvatarFile($avatarFile);
            }

            $this->accountService->updateProfile( $data );

            $this->addFlash('success', 'Profil mis à jour avec succès');
        }

        return $this->render( 'pages/public/account/profile.html.twig', [
            'formProfile' => $formProfile->createView(),
        ]);
    }

    #[Route('/mon-compte/remove-avatar', name: 'remove_avatar', methods: ['POST'])]
    public function removeAvatar(): Response
    {
        $user = $this->getUserOrThrow();
        $user->setAvatar(null);
        // $user->setAvatarFile(null); // pas forcément requis,
        // on flush
        $this->em->flush();

        $this->addFlash('success', 'Photo de profil supprimée avec succès');
        return $this->redirectToRoute('app_account_profile');
        // ou 'app_account_index' si tu préfères
    }


    #[Route( '/securite' , name: 'security' )]
    #[IsGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function security( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();

        // Delete account form
        $formDeleteAccount = $this->createForm( DeleteAccountForm::class );
        $formDeleteAccount->handleRequest( $request );
        if ( $formDeleteAccount->isSubmitted() && $formDeleteAccount->isValid() ) {
            $data = $formDeleteAccount->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['password'] ) ) {
                $this->addFlash( 'error', 'Impossible de supprimer votre compte, mot de passe invalide' );
                return $this->redirectToRoute( 'app_account_security' );
            }

            try {
                $this->deleteAccountService->requestAccountDeletion( $user, $request );
            } catch ( \LogicException $e ) {
                $this->addFlash( 'error', $e->getMessage() );
                return $this->redirectToRoute( 'app_account_security' );
            }

            $this->addFlash( 'info', 'Votre demande de suppression de compte a bien été prise en compte' );
        }

        return $this->render( 'pages/public/account/security.html.twig', [
            'formDeleteAccount' => $formDeleteAccount->createView(),
        ]);
    }

    #[Route( '/securite/email' , name: 'security_email' )]
    #[IsGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function changeEmail( Request $request ) : Response
    {
        // Profile update form
        $user = $this->getUserOrThrow();

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

        // latest email change request for the user
        $requestEmailChange = $this->emailChangeService->getLatestValidEmailVerification( $user );

        return $this->render( 'pages/public/account/security_email.html.twig', [
            'formEmail'   => $formEmail->createView(),
            'requestEmailChange' => $requestEmailChange,
        ] );
    }

    #[Route( path: '/resend-verification-email', name: 'resend_verification_email', methods: ['GET'] )]
    public function resendVerificationEmail( EventDispatcherInterface $dispatcher ) : Response
    {
        $user = $this->getUserOrThrow();

        $dispatcher->dispatch( new AccountVerificationRequestEvent( $user ) );

        $this->addFlash( 'success', 'Email de vérification envoyé' );

        return $this->redirectToRoute( 'app_account_security');
    }

    #[Route( '/securite/mot-de-passe' , name: 'security_password' )]
    #[IsGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function passwordChange( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();

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

        return $this->render( 'pages/public/account/security_password.html.twig', [
            'formPassword' => $formPassword->createView(),
        ] );
    }

    #[Route('/historique', name: 'history')]
    public function history(): Response
    {
        $user = $this->getUserOrThrow();

        $watchlist = $this->historyService->getLastWatchedContent($user);

        return $this->render('pages/public/account/history.html.twig', [
            'watchlist' => $watchlist,
        ]);
    }

    #[Route('/badges', name: 'badges')]
    public function badges(): Response
    {
        $user = $this->getUserOrThrow();

        $badges = $this->badgeService->getBadges();
        $unlocks = $this->badgeService->getUnlocksForUser($user);

        // Grouper les badges par action
        $groupedBadges = [];

        foreach ($badges as $badge) {
            $groupedBadges[$badge->getAction()][] = $badge;
        }

        // Trier chaque groupe par actionCount croissant
        foreach ($groupedBadges as &$group) {
            usort($group, function (Badge $a, Badge $b) {
                return $a->getActionCount() <=> $b->getActionCount();
            });
        }
        unset($group); // pour éviter toute modification par référence accidentelle

        return $this->render('pages/public/account/badges.html.twig', [
            'groupedBadges' => $groupedBadges,
            'unlocks' => $unlocks,
            'actionLabels' => BadgeAction::getLabels()
        ]);
    }

    #[Route( '/gestion-abonnement', name: 'subscription_invoices', methods: ['GET', 'POST'] )]
    public function subscriptionsAndInvoices(Request $request, SubscriptionRepository $repository) : Response
    {
        $user = $this->getUserOrThrow();

        $subscription = $repository->findCurrentForUser($user);
        $transactions = $this->transactionRepository->findfor($user);

        if ($request->request->has('saveInvoiceInfo')) {
            $content = (string) $request->request->get('invoiceInfo', '');

            $user->setInvoiceInfo($content);
            $this->em->flush();

            $this->addFlash('success', 'Enregistré avec succès');
        }

        return $this->render('pages/public/account/subscription_invoices.html.twig', [
            'transactions' => $transactions,
            'subscription' => $subscription,
        ]);
    }


    #[Route( '/gestion-notifications', name: 'notifications_settings' )]
    public function notificationsSettings() : Response
    {
        return $this->render( 'pages/public/account/notifications_settings.html.twig', []);
    }

    #[Route('/emails-preferences', name: 'emails_preferences' )]
    #[IsGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function emailSettings() : Response
    {
        return $this->render( 'pages/public/account/email-settings.html.twig');
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

    #[Route(path: '/abonnement/stripe', name: 'manage_subscription', methods: ['POST'])]
    public function manage(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $redirectUrl = $this->generateUrl('app_account_subscription_invoices', [], UrlGeneratorInterface::ABSOLUTE_URL);
        if (null === $user->getStripeId()) {
            $this->addFlash('error', "Vous n'avez pas d'abonnement actif");

            return $this->redirect($redirectUrl);
        }

        return $this->redirect($this->api->getBillingUrl($user, $redirectUrl));
    }
}
