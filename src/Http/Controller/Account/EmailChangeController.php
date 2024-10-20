<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Service\EmailChangeService;
use App\Domain\Auth\Registration\Entity\EmailVerification;
use App\Http\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailChangeController extends AbstractController
{
    public function __construct(
        private readonly EmailChangeService $emailChangeService
    )
    {
    }

    #[Route( '/email/modification/{token}', name: 'user_email_confirm' )]
    #[ParamConverter( 'emailVerification', options: ['mapping' => ['token' => 'token']] )]
    public function confirmEmail( EmailVerification $emailVerification = null ) : Response
    {
        if ( !$emailVerification ) {
            return $this->handleInvalidOrExpiredToken( 'Le lien de changement d\'email est invalide');
        }

        if ( $emailVerification->isExpired() ) {
            return $this->handleInvalidOrExpiredToken( 'Le lien de changement d\'email a expiré' );
        }

        return $this->handleValidToken( $emailVerification );
    }

    private function handleInvalidOrExpiredToken( string $message ) : Response
    {
        $this->addFlash( 'danger', $message );
        return $this->redirectToRoute('app_login');
    }


    private function handleValidToken( EmailVerification $emailVerification ) : Response
    {
        $this->emailChangeService->confirmEmailChange( $emailVerification );
        $this->addFlash( 'success', 'Votre email a été mis à jour avec succès' );
        return $this->redirectToRoute('app_login');
    }
}
