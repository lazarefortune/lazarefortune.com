<?php

namespace App\Domain\Auth\Core\Subscriber;

use App\Domain\Auth\Event\RequestEmailChangeEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Email;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailService $mailService,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            RequestEmailChangeEvent::class => 'onEmailChangeRequest',
        ];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function onEmailChangeRequest( RequestEmailChangeEvent $event ) : void
    {
        $user = $event->emailVerification->getAuthor();

        // Email the new email address
        $email = $this->mailService->createEmail( 'mails/auth/account/confirm-email-change.twig', [
            'token' => $event->emailVerification->getToken(),
            'user' => $user,
        ] )
            ->to( $event->emailVerification->getEmail() )
            ->subject( 'Vérification de votre nouvelle adresse email' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );

        // Email the current email address
        $emailNotification = $this->mailService->createEmail( 'mails/auth/account/notify-email-change.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Changement d\'adresse email' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $emailNotification );
    }
}