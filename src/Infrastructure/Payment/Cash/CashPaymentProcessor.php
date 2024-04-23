<?php

namespace App\Infrastructure\Payment\Cash;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\PaymentProcessorInterface;
use App\Domain\Payment\PaymentResultDone;

class CashPaymentProcessor implements PaymentProcessorInterface
{

    const PAYMENT_METHOD = 'cash';

    public function supports( Payment $payment ) : bool
    {
        return $payment->getPaymentMethod() === self::PAYMENT_METHOD;
    }

    public function processPayment( Payment $payment, Appointment $appointment ) : PaymentResultDone
    {
        // TODO: Implement processPayment() method.
        return new PaymentResultDone( true, 'Payment processed successfully' );
    }
}