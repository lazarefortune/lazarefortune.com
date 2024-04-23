<?php

namespace App\Domain\Profile\Event;

use App\Domain\Auth\Entity\User;

class UserDeleteRequestEvent
{
    public function __construct(
        private readonly User $user,
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}