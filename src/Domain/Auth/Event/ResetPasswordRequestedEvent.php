<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\PasswordReset;
use Symfony\Contracts\EventDispatcher\Event;

class ResetPasswordRequestedEvent extends Event
{
    public const NAME = 'reset.password.requested';

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