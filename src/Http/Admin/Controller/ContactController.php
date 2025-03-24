<?php

namespace App\Http\Admin\Controller;

use App\Domain\Contact\Entity\Contact;
use App\Domain\Contact\Repository\ContactRepository;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/contact', name: 'contact_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        protected PaginatorInterface       $paginator,
    )
    {
    }

    #[Route( '/', name: 'index' )]
    public function index() : Response
    {
        $query = $this->contactRepository->findLatestQuery();
        $contacts = $this->paginator->paginate( $query );

        return $this->render( 'pages/admin/contact/index.html.twig', [
            'contacts' => $contacts,
        ] );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        if (!$contact->isRead()) {
            $contact->setIsRead(true);
            $this->contactRepository->save($contact, true);
        }

        return $this->render('pages/admin/contact/show.html.twig', [
            'contact' => $contact
        ]);
    }

    #[Route('/{id}/unread', name: 'unread')]
    public function markAsUnread(Contact $contact): Response
    {
        // On set isRead à false
        $contact->setIsRead(false);
        $this->contactRepository->save($contact, true);

        // On redirige vers la boîte de réception
        return $this->redirectToRoute('admin_contact_index');
    }

}