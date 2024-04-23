<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;

class EmailConfirmationCompletedEvent
{
    const NAME = 'email.confirm.success';

    public function __construct( protected User $user )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}