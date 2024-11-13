<?php

namespace App\Domain\Auth\Core\Subscriber;

use App\Domain\Auth\Core\Event\UserUpdatedEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            UserUpdatedEvent::NAME => 'onUserUpdated',
        ];
    }

    public function onUserUpdated( UserUpdatedEvent $event ) : void
    {
        $oldUser = $event->getOldUser();
        $newUser = $event->getNewUser();

        $newRoles = array_diff( $newUser->getRoles(), $oldUser->getRoles() );

        foreach ( $newRoles as $role ) {
            switch ( $role ) {
                case 'ROLE_ADMIN':
                    try {
                        $email = $this->mailService->createEmail('mails/auth/account/admin-role-added.twig', [
                            'user' => $newUser,
                            'home_url' => $this->urlGenerator->generate('admin_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        ])
                            ->to($newUser->getEmail())
                            ->subject('Vous êtes maintenant administrateur');

                        $this->mailService->send($email);
                    } catch ( \Exception $e ) {
                        throw new \Exception($e->getMessage());
                    }
                    break;
                case 'ROLE_AUTHOR':
                    try {
                        $email = $this->mailService->createEmail('mails/auth/account/author-role-added.twig', [
                           'user' => $newUser,
                           'author_dashboard_url' => $this->urlGenerator->generate('admin_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        ])
                        ->to($newUser->getEmail())
                        ->subject('Vous êtes maintenant auteur');

                        $this->mailService->send($email);

                    } catch ( \Exception $e ) {
                        throw new \Exception($e->getMessage());
                    }
                    break;
            }
        }
    }

}