<?php

namespace App\Domain\Payment;

class PaymentResultUrl extends PaymentResult
{
    public function __construct(
        private readonly bool   $success,
        private readonly string $message,
        private readonly string $url,
    )
    {
        parent::__construct( $success, $message );
    }

    public function getUrl() : string
    {
        return $this->url;
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