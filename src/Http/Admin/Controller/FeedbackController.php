<?php

namespace App\Http\Admin\Controller;

use App\Domain\Feedback\Entity\Feedback;
use App\Domain\Feedback\Repository\FeedbackRepository;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

#[Route( '/feedback', name: 'feedback_' )]
class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly FeedbackRepository $feedbackRepository,
        protected PaginatorInterface       $paginator,
    )
    {
    }

    #[Route( '/', name: 'index', methods: ['GET'] )]
    public function index( Request $request ): Response
    {
        $query = $this->feedbackRepository->findLatestQuery();
        $feedbacks = $this->paginator->paginate( $query );

        return $this->render( 'pages/admin/feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ] );
    }

    #[Route( '/details/{id}', name: 'show', methods: ['GET', 'POST'] )]
    public function show( Feedback $feedback, Registry $workflows, EntityManagerInterface $em ): Response
    {
        $workflow = $workflows->get($feedback, 'feedback_status');

        if ($workflow->can($feedback, 'mark_read')) {
            $workflow->apply($feedback, 'mark_read');
            $em->flush();
        }

        return $this->render( 'pages/admin/feedback/show.html.twig', [
            'feedback' => $feedback,
        ] );
    }

    #[Route('/{id}/resolve', name: 'resolve', methods: ['POST'])]
    public function resolve(
        Feedback $feedback,
        Registry $workflows,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('resolve' . $feedback->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('feedback_show', ['id' => $feedback->getId()]);
        }

        $workflow = $workflows->get($feedback, 'feedback_status');

        if ($workflow->can($feedback, 'mark_resolved')) {
            $workflow->apply($feedback, 'mark_resolved');
            $em->flush();
            $this->addFlash('success', 'Le feedback a été marqué comme résolu.');
        } else {
            $this->addFlash('warning', 'Impossible de résoudre ce feedback.');
        }

        return $this->redirectToRoute('admin_feedback_show', ['id' => $feedback->getId()]);
    }
}