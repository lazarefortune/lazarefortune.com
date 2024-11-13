<?php

namespace App\Domain\Auth\Core\Event;

use App\Domain\Auth\Core\Entity\User;

class UserUpdatedEvent
{
    public const NAME = 'user.updated';

    public function __construct(
        private readonly User $newUser,
        private readonly User $oldUser
    ){}

    public function getNewUser(): User
    {
        return $this->newUser;
    }

    public function getOldUser(): User
    {
        return $this->oldUser;
    }
}