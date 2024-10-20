<?php

namespace App\Domain\Auth\Password\Subscriber;

use App\Domain\Auth\Password\Event\PasswordResetRequestedEvent;
use App\Domain\Auth\Password\Event\PasswordUpdatedEvent;
use App\Domain\Auth\Password\Service\PasswordService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PasswordService $passwordService
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            PasswordUpdatedEvent::NAME => 'onPasswordUpdated',
            PasswordResetRequestedEvent::NAME => 'onPasswordResetRequested'
        ];
    }

    public function onPasswordUpdated( PasswordUpdatedEvent $passwordUpdatedEvent ):void
    {
        $user = $passwordUpdatedEvent->getUser();

        try {
            $this->passwordService->sendPasswordUpdatedEmail( $user );
        } catch (\Exception) {
            throw new \Exception('Une erreur est survenue lors de l\'envoi de l\'email');
        }
    }

    public function onPasswordResetRequested( PasswordResetRequestedEvent $passwordResetRequestedEvent ):void
    {
        $passwordResetRequest = $passwordResetRequestedEvent->getPasswordReset();

        try {
            $this->passwordService->sendPasswordResetRequestedEmail( $passwordResetRequest );
        } catch (\Exception) {
            throw new \Exception('Une erreur est survenue lors de l\'envoi de l\'email');
        }
    }
}