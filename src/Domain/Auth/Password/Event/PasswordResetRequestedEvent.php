<?php

namespace App\Domain\Auth\Password\Event;

use App\Domain\Auth\Password\Entity\PasswordReset;
use Symfony\Contracts\EventDispatcher\Event;

class PasswordResetRequestedEvent extends Event
{
    public const NAME = 'password.reset.requested';

    public function __construct(
        public PasswordReset $passwordReset,
    )
    {
    }

    public function getPasswordReset() : PasswordReset
    {
        return $this->passwordReset;
    }
}