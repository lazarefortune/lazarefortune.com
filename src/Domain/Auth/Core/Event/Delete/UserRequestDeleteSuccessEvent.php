<?php

namespace App\Domain\Auth\Core\Event\Delete;

use App\Domain\Auth\Core\Entity\User;

class UserRequestDeleteSuccessEvent
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