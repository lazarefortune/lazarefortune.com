<?php

namespace App\Domain\Payment\Repository;

use App\Domain\Payment\Entity\Transaction;
use App\Helper\Paginator\PaginatorInterface;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Transaction>
 */
class TransactionRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Transaction::class );
    }
}