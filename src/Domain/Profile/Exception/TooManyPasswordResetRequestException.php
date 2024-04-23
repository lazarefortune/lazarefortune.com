<?php

namespace App\Domain\Profile\Exception;

use App\Domain\Auth\Entity\PasswordReset;

class TooManyPasswordResetRequestException extends \Exception
{
    public function __construct( public PasswordReset $passwordReset )
    {
        parent::__construct();
    }
}