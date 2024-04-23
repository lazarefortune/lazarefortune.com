<?php

namespace App\Domain\Payment;

class PaymentResultDone extends PaymentResult
{
    public function __construct(
        private readonly bool   $success,
        private readonly string $message,
    )
    {
        parent::__construct( $success, $message );
    }

    public function isSuccess() : bool
    {
        return $this->success;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

}