<?php

namespace App\Domain\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionCompletedEvent extends Event
{
    public function __construct(
        private readonly string $transactionId,
    )
    {
    }

    public function getTransactionId() : string
    {
        return $this->transactionId;
    }
}