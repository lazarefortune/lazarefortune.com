<?php

namespace App\Domain\Collaboration\Subscriber;

use App\Domain\Collaboration\Event\CollaborationRequestAcceptedEvent;
use App\Domain\Collaboration\Event\CollaborationRequestCreatedEvent;
use App\Domain\Collaboration\Event\CollaborationRequestRejectedEvent;
use App\Infrastructure\Mailing\MailService;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CollaborationRequestSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly MailService $mailService,
        private readonly string      $adminEmail,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            CollaborationRequestCreatedEvent::class => 'onCollaborationRequestCreated',
            CollaborationRequestAcceptedEvent::class => 'onCollaborationRequestAccepted',
            CollaborationRequestRejectedEvent::class => 'onCollaborationRequestRejected',
        ];
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws Exception
     */
    public function onCollaborationRequestCreated( CollaborationRequestCreatedEvent $event ) : void
    {
        if ( !$this->adminEmail ) {
            throw new Exception( 'Adresse email administrateur invalide' );
        }

        $user = $event->getCollaborationRequest()->getRequester();

        // Admin email
        $adminEmail = $this->mailService->prepareEmail(
            $this->adminEmail,
            'Demande de collaboration reçue de ' . $user->getFullName(),
            'mails/admin/collaboration/request-received.twig', [
            'request' => $event->getCollaborationRequest(),
            'adminRequestUrl' => $this->urlGenerator->generate( 'admin_collaboration_request_show', [
                'id' => $event->getCollaborationRequest()->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL ),
        ] );

        $this->mailService->send( $adminEmail );

        // Email the new email address
        $email = $this->mailService->createEmail( 'mails/collaboration/request/confirm.twig', [
            'request' => $event->getCollaborationRequest(),
            'userRequestUrl' => $this->urlGenerator->generate( 'app_collaboration_request_show', [
                'id' => $event->getCollaborationRequest()->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL ),
        ] )
            ->to( $user->getEmail() )
            ->subject( 'La demande de collaboration a bien été reçue' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function onCollaborationRequestAccepted( CollaborationRequestAcceptedEvent $event ) : void
    {
        $user = $event->getCollaborationRequest()->getRequester();

        $email = $this->mailService->createEmail( 'mails/collaboration/request/accept.twig', [
            'request' => $event->getCollaborationRequest(),
            'userProfileUrl' => $this->urlGenerator->generate( 'app_account_profile', [],
                UrlGeneratorInterface::ABSOLUTE_URL ),
            ] )
            ->to( $user->getEmail() )
            ->subject( 'Demande de collaboration acceptée' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function onCollaborationRequestRejected( CollaborationRequestRejectedEvent $event ) : void
    {
        $user = $event->getCollaborationRequest()->getRequester();

        $email = $this->mailService->createEmail( 'mails/collaboration/request/reject.twig', [
            'request' => $event->getCollaborationRequest(),
            'supportUrl' => $this->urlGenerator->generate( 'app_contact' , [], UrlGeneratorInterface::ABSOLUTE_URL ),
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Demande de collaboration refusée' )
            ->priority( Email::PRIORITY_HIGH );

        $this->mailService->send( $email );
    }
}