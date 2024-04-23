<?php

namespace App\Domain\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Domain\Payment\Entity\Payment;

class PaymentEvent extends Event
{
    public function __construct( private readonly Payment $payment )
    {
    }

    public function getPayment() : Payment
    {
        return $this->payment;
    }

}