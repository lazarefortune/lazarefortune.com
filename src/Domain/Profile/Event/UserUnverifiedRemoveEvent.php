<?php

namespace App\Domain\Profile\Event;

use App\Domain\Auth\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserUnverifiedRemoveEvent extends Event
{
    public function __construct(
        public User $user,
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}