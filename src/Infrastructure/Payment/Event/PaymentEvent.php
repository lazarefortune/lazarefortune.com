<?php

namespace App\Infrastructure\Payment\Event;

use App\Domain\Auth\Core\Entity\User;
use App\Infrastructure\Payment\Payment;

class PaymentEvent
{
    public function __construct(private readonly Payment $payment, private readonly User $user)
    {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}