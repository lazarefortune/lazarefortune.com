<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegistrationCompletedEvent extends Event
{
    const NAME = 'user.created';

    public function __construct( protected User $user )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}