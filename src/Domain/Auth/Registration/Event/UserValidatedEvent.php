<?php

namespace App\Domain\Auth\Registration\Event;

use App\Domain\Auth\Core\Entity\User;

class UserValidatedEvent
{
    public const NAME = 'user.validated';

    public function __construct(
        public readonly User $user,
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}