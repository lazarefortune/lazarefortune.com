<?php

namespace App\Domain\Auth\Password\Event;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class PasswordUpdatedEvent extends Event
{
    public const NAME = 'password.updated';

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