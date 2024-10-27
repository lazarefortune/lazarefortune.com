<?php

namespace App\Domain\Auth\Registration\Subscriber;

use App\Domain\Auth\Registration\Event\UserCreatedEvent;
use App\Domain\Auth\Registration\Service\RegistrationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RegistrationService $registrationService
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            UserCreatedEvent::NAME => 'onUserRegistered',
        ];
    }

    /**
     * Send welcome email and verification email to the user
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function onUserRegistered( UserCreatedEvent $event ) : void
    {
        $user = $event->getUser();

        $this->registrationService->sendWelcomeEmail( $user );
    }
}