<?php

namespace App\Domain\Account\Event;

use App\Domain\Auth\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AccountDeletedEvent extends Event
{
    public const NAME = 'delete.client';

    public function __construct( protected User $user )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}