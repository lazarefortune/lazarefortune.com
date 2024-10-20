<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class EmailConfirmationRequestedEvent extends Event
{
    public const NAME = 'email.confirm.requested';

    public function __construct( protected User $user )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}