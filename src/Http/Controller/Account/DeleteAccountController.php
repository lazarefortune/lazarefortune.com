<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Form\DeleteAccountForm;
use App\Domain\Auth\Core\Service\DeleteAccountService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/mon-compte', name: 'account_' )]
#[IsGranted( 'ROLE_USER' )]
class DeleteAccountController extends AbstractController
{
    public function __construct(
        private readonly DeleteAccountService        $deleteAccountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    #[Route( '/supprimer-mon-compte', name: 'delete' )]
    public function deleteAccount( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( DeleteAccountForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            $data = $form->getData();
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

        return $this->render( 'pages/public/account/delete.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( '/supprimer-mon-compte/annuler', name: 'delete_cancel' )]
    public function cancelDeleteAccount( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $this->deleteAccountService->cancelAccountDeletion( $user );

        $this->addFlash( 'success', 'Suppression de compte annulée avec succès' );

        return $this->redirectToRoute( 'app_account_profile' );
    }
}