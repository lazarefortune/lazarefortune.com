<?php

namespace App\Domain\Auth\Registration\Event;

use App\Domain\Auth\Core\Entity\User;

class UserCreatedEvent
{

    const NAME = 'user.created';

    public function __construct(
        private readonly User $user
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}