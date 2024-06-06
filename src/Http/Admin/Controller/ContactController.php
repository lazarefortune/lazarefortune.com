<?php

namespace App\Http\Admin\Controller;

use App\Domain\Contact\Repository\ContactRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/contact', name: 'contact_' )]
#[IsGranted('ROLE_ADMIN')]
class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository
    )
    {
    }

    #[Route( '/', name: 'index' )]
    public function index()
    {
        $contacts = $this->contactRepository->findAll();

        return $this->render( 'admin/contact/index.html.twig', [
            'contacts' => $contacts,
        ] );
    }
}