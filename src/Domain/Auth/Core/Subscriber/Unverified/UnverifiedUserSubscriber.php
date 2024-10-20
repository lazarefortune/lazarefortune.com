<?php

namespace App\Domain\Auth\Core\Subscriber\Unverified;

use App\Domain\Auth\Core\Event\Unverified\AccountVerificationRequestEvent;
use App\Domain\Auth\Core\Event\Unverified\DeleteUnverifiedUserSuccessEvent;
use App\Domain\Auth\Core\Event\Unverified\PreviousDeleteUnverifiedUserEvent;
use App\Domain\Auth\Registration\Verifier\EmailVerifier;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UnverifiedUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly EmailVerifier         $emailVerifier,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            PreviousDeleteUnverifiedUserEvent::class => 'onPreviousDeleteUnverifiedUser',
            DeleteUnverifiedUserSuccessEvent::class => 'onDeleteUnverifiedUserSuccess',
            AccountVerificationRequestEvent::class => 'onAccountVerificationRequest',
        ];
    }

    public function onPreviousDeleteUnverifiedUser( PreviousDeleteUnverifiedUserEvent $event ) : void
    {
        $user = $event->getUser();

        $signatureComponents = $this->emailVerifier->generateSignature( $user );

        $data = [
            'user' => $user,
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Votre compte va bientôt être supprimé',
            'mails/account/unverified-user/previous-delete.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    public function onDeleteUnverifiedUserSuccess( DeleteUnverifiedUserSuccessEvent $event ) : void
    {
        $user = $event->getUser();

        $email = $this->mailService->createEmail( 'mails/account/unverified-user/deleted-success.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre compte a été supprimé' );

        $this->mailService->send( $email );
    }

    public function onAccountVerificationRequest( AccountVerificationRequestEvent $event ) : void
    {
        $user = $event->getUser();

        $signatureComponents = $this->emailVerifier->generateSignature( $user );

        $data = [
            'user' => $user,
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Plus qu\'une étape pour valider votre compte',
            'mails/account/unverified-user/verification-request.twig',
            $data
        );

        $this->mailService->send( $email );
    }
}