<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\EmailVerification;
use App\Domain\Profile\Service\ProfileService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailChangeController extends AbstractController
{
    public function __construct(
        private readonly ProfileService $profileService,
    )
    {
    }

    #[Route( '/email/change/{token}', name: 'user_email_confirm' )]
    #[ParamConverter( 'emailVerification', options: ['mapping' => ['token' => 'token']] )]
    public function confirmEmail( EmailVerification $emailVerification = null ) : Response
    {
        if ( !$emailVerification ) {
            return $this->handleInvalidOrExpiredToken( 'Le lien de changement d\'email est invalide' );
        }

        if ( $emailVerification->isExpired() ) {
            return $this->handleInvalidOrExpiredToken( 'Le lien de changement d\'email a expiré' );
        }

        return $this->handleValidToken( $emailVerification );
    }

    private function handleInvalidOrExpiredToken( string $message ) : Response
    {
        $this->addFlash( 'danger', $message );
        return $this->render( 'pages/message.html.twig' );
    }


    private function handleValidToken( EmailVerification $emailVerification ) : Response
    {
        $this->profileService->updateEmail( $emailVerification );
        $this->addFlash( 'success', 'Votre email a été mis à jour avec succès' );

        return $this->render( 'pages/message.html.twig' );
    }
}
