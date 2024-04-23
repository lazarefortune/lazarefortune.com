<?php

namespace App\Http\Admin\Controller;

use App\Domain\Payment\Service\TransactionService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/transactions', name: 'transaction_' )]
#[IsGranted( 'ROLE_SUPER_ADMIN' )]
class TransactionController extends AbstractController
{
    public function __construct( private readonly TransactionService $transactionService )
    {
    }

    #[Route( '/', name: 'index' )]
    public function index() : Response
    {
        $transactions = $this->transactionService->getTransactions();
        return $this->render( 'admin/transaction/index.html.twig', [
            'transactions' => $transactions
        ] );
    }

    #[Route( '/{id}', name: 'show' )]
    public function show( int $id ) : Response
    {
        $transaction = $this->transactionService->getTransaction( $id );
        return $this->render( 'admin/transaction/show.html.twig', [
            'transaction' => $transaction
        ] );
    }
}