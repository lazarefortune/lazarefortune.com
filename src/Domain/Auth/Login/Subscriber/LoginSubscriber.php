<?php

namespace App\Domain\Auth\Login\Subscriber;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Login\Service\LoginService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoginService $loginService
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            "security.authentication.success" => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess( AuthenticationSuccessEvent $event ) : void
    {
        /* @var $user User */
        $user = $event->getAuthenticationToken()->getUser();
        $this->loginService->setLastLogin( $user );
    }
}