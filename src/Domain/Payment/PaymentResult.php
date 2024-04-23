<?php

namespace App\Domain\Payment;

abstract class PaymentResult
{
    public function __construct(
        private readonly bool   $success,
        private readonly string $message,
    )
    {
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