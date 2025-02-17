<?php

namespace App\Http\Controller\CollaborationRequest;

use App\Http\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/demandes-de-collaboration', name: 'collaboration_request_')]
#[IsGranted('ROLE_USER')]
class CollaborationRequestController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index() : Response
    {
        return $this->render('pages/public/collaboration-request/index.html.twig');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show() : Response
    {
        return $this->render('pages/public/collaboration-request/show.html.twig');
    }
}
