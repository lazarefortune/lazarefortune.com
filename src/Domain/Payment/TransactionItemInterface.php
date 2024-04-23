<?php

namespace App\Domain\Payment;

interface TransactionItemInterface
{
    public function getId() : ?int;

    public function getAmount() : ?float;

    public function getItemName() : ?string;

    public function getItemDescription() : ?string;
}