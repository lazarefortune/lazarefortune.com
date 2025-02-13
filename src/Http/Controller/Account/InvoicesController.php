<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Premium\Repository\TransactionRepository;
use App\Http\Controller\AbstractController;
use App\Http\Requirements;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method User getUser()
 */
class InvoicesController extends AbstractController
{
    public function __construct(private readonly TransactionRepository $repository)
    {
    }

    #[Route(path: '/mon-compte/factures', name: 'user_invoices', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $transactions = $this->repository->findfor($this->getUser());

        return $this->render('pages/public/account/invoices.html.twig', [
            'transactions' => $transactions,
            'menu' => 'account',
        ]);
    }

    #[Route(path: '/mon-compte/factures', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateInfo(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $content = (string) $request->request->get('invoiceInfo');
        $user = $this->getUserOrThrow();
        $user->setInvoiceInfo($content);
        $em->flush();
        $this->addFlash('success', 'Vos informations ont bien été enregistrées');

        return $this->redirectToRoute('app_user_invoices');
    }

    #[Route(path: '/mon-compte/factures/{id}', name: 'user_invoice', requirements: ['id' => Requirements::ID])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): Response
    {
        $transaction = $this->repository->findOneBy([
            'id' => $id,
            'author' => $this->getUser(),
        ]);

        if (null === $transaction) {
            throw new NotFoundHttpException();
        }

        return $this->render('pages/public/account/invoice.html.twig', [
            'transaction' => $transaction,
        ]);
    }
}