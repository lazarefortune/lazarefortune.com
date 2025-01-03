<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Registration\Entity\EmailVerification;
use Symfony\Contracts\EventDispatcher\Event;

class RequestEmailChangeEvent extends Event
{
    public function __construct( public EmailVerification $emailVerification )
    {
    }
}