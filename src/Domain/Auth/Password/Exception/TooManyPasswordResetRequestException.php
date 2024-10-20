<?php

namespace App\Domain\Auth\Password\Exception;

use App\Domain\Auth\Password\Entity\PasswordReset;

class TooManyPasswordResetRequestException extends \Exception
{
    public function __construct( public PasswordReset $passwordReset )
    {
        parent::__construct();
    }
}