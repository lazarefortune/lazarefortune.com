<?php

namespace App\Domain\Account\Subscriber;

use App\Domain\Account\Event\AccountDeletedEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailService           $mailService,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            AccountDeletedEvent::NAME => 'onAccountDeleted',
        ];
    }

    public function onAccountDeleted( AccountDeletedEvent $event ) : void
    {
        $client = $event->getUser();

        $email = $this->mailService->createEmail( 'mails/account/account-deleted.twig', [
            'client' => $client,
            'contact_url' => $this->urlGenerator->generate(
                'app_contact',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Votre compte a été supprimé' );

        $this->mailService->send( $email );
    }
}