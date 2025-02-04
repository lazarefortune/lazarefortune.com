<?php

namespace App\Domain\Application\Message;

class SendTestEmailMessage
{
    public function __construct(
        private readonly string $email
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }
}
