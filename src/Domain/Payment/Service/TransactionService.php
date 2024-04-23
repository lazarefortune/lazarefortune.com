<?php

namespace App\Domain\Payment\Service;

use App\Domain\Payment\Repository\TransactionRepository;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    )
    {
    }

    public function getTransactions()
    {
        return $this->transactionRepository->findAll();
    }

    public function getTransaction( int $id )
    {
        return $this->transactionRepository->find( $id );
    }
}