<?php

namespace App\Http\Controller\Feedback;


use App\Domain\Feedback\Enum\FeedbackType;
use App\Domain\Feedback\Form\FeedbackForm;
use App\Domain\Feedback\Service\FeedbackService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/feedback', name: 'feedback_' )]
class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly FeedbackService $feedbackService,
    )
    {
    }

    #[Route( '/proposer-une-idee', name: 'idea' )]
    public function ideaFeedback( Request $request ) : Response
    {
        $feedback = $this->feedbackService->prepareFeedback( FeedbackType::IDEA );

        $form = $this->createForm( FeedbackForm::class, $feedback );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $this->feedbackService->save( $feedback );

            $this->addFlash( 'success', 'Merci pour ton idée, elle a bien été enregistrée !' );

            return $this->redirectToRoute( 'app_feedback_idea' );
        }

        return $this->render( 'pages/public/feedback/idea.html.twig', [
            'ideaFeedbackForm' => $form->createView(),
        ] );
    }


    #[Route('/signaler-un-bug', name: 'bug')]
    public function bugFeedback(Request $request): Response
    {
        $feedback = $this->feedbackService->prepareFeedback(FeedbackType::BUG);

        $form = $this->createForm(FeedbackForm::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->feedbackService->save($feedback);

            $this->addFlash('success', 'Merci d’avoir signalé ce bug ! On va jeter un œil');

            return $this->redirectToRoute('app_feedback_bug');
        }

        return $this->render('pages/public/feedback/bug.html.twig', [
            'bugFeedbackForm' => $form->createView(),
        ]);
    }

}