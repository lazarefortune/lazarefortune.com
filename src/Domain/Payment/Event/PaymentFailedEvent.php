<?php

namespace App\Domain\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PaymentFailedEvent extends Event
{
    public function __construct(
        private readonly string $paymentId,
    )
    {
    }

    public function getPaymentId() : string
    {
        return $this->paymentId;
    }
}