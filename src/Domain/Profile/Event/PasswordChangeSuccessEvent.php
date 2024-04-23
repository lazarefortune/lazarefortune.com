<?php

namespace App\Domain\Profile\Event;

use App\Domain\Auth\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class PasswordChangeSuccessEvent extends Event
{
    public const NAME = 'password.change.success';

    public function __construct(
        public readonly User $user
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}