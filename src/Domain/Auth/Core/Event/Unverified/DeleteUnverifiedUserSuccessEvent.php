<?php

namespace App\Domain\Auth\Core\Event\Unverified;

use App\Domain\Auth\Core\Entity\User;

class DeleteUnverifiedUserSuccessEvent
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