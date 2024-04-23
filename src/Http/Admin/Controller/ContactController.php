<?php

namespace App\Http\Admin\Controller;

use App\Domain\Contact\Repository\ContactRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/contact', name: 'contact_' )]
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