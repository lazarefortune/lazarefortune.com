<?php

namespace App\Domain\Profile\Subscriber;

use App\Domain\Auth\Event\RequestEmailChangeEvent;
use App\Domain\Profile\Event\PasswordChangeSuccessEvent;
use App\Domain\Profile\Event\UserUnverifiedRemoveEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Email;

class ProfileSubscriber implements EventSubscriberInterface
{

    public function __construct( private readonly MailService $mailService )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            RequestEmailChangeEvent::class => 'onEmailChangeRequest',
            PasswordChangeSuccessEvent::class => 'onPasswordChanged',
            UserUnverifiedRemoveEvent::class => 'onUserUnverifiedRemove',
        ];
    }

    public function onEmailChangeRequest( RequestEmailChangeEvent $event ) : void
    {
        $user = $event->emailVerification->getAuthor();
        // On envoie un email de vérification
        $email = $this->mailService->createEmail( 'mails/account/confirm-email-change.twig', [
            'token' => $event->emailVerification->getToken(),
            'username' => $user->getFullname(),
        ] )
            ->to( $event->emailVerification->getEmail() )
            ->subject( 'Vérification de votre nouvelle adresse email' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }

    public function onPasswordChanged( PasswordChangeSuccessEvent $event ) : void
    {
        $user = $event->getUser();

        $email = $this->mailService->createEmail( 'mails/account/password-updated.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre mot de passe a été modifié' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }

    public function onUserUnverifiedRemove( UserUnverifiedRemoveEvent $event ) : void
    {
        $user = $event->getUser();

        $email = $this->mailService->createEmail( 'mails/account/unverified-removed.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre compte a été supprimé' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }
}