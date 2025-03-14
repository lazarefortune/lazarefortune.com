<?php

namespace App\Http\Admin\Controller;

use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Domain\Collaboration\Repository\CollaborationRequestRepository;
use App\Domain\Collaboration\Service\CollaborationRequestService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[Route('/demandes-de-collaboration', name: 'collaboration_request_')]
class CollaborationRequestController extends AbstractController
{
    public function __construct(
        private readonly CollaborationRequestService $collaborationRequestService
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index( CollaborationRequestRepository $collaborationRequestRepository ) : Response
    {
        $collaborationRequests = $collaborationRequestRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pages/admin/collaboration_request/index.html.twig', [
            'collaborationRequests' => $collaborationRequests,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show( CollaborationRequest $collaborationRequest ) : Response
    {
        return $this->render('pages/admin/collaboration_request/show.html.twig', [
            'collaborationRequest' => $collaborationRequest,
        ]);
    }

    #[Route('/{id}/accepter', name: 'accept', methods: ['POST'])]
    public function accept( Request $request, CollaborationRequest $collaborationRequest ) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $message = $request->request->get('message');

        if ( $message ) {
            $collaborationRequest->setResponseMessage( $message );
        }

        $this->collaborationRequestService->accept( $collaborationRequest );

        $this->addFlash('success', 'Proposition de collaboration acceptée');
        return $this->redirectToRoute('admin_collaboration_request_index');
    }

    #[Route('/{id}/refuser', name: 'reject', methods: ['POST'])]
    public function reject( Request $request, CollaborationRequest $collaborationRequest ) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $message = $request->request->get('message');

        if ( $message ) {
            $collaborationRequest->setResponseMessage( $message );
        }

        $this->collaborationRequestService->reject( $collaborationRequest );

        $this->addFlash('success', 'Proposition de collaboration refusée');
        return $this->redirectToRoute('admin_collaboration_request_index');
    }
}