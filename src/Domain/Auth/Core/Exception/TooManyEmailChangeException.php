<?php

namespace App\Domain\Auth\Core\Exception;

use App\Domain\Auth\Registration\Entity\EmailVerification;

class TooManyEmailChangeException extends \Exception
{
    public function __construct( public EmailVerification $emailVerification )
    {
        parent::__construct();
    }
}