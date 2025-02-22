<?php

namespace App\Http\Controller\CollaborationRequest;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Domain\Collaboration\Enum\CollaborationRequestRole;
use App\Domain\Collaboration\Exception\AlreadyExistCollaborationRequestException;
use App\Domain\Collaboration\Exception\InvalidRoleRequestException;
use App\Domain\Collaboration\Form\CollaborationRequestForm;
use App\Domain\Collaboration\Service\CollaborationRequestService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/demandes-de-collaboration', name: 'collaboration_request_')]
#[IsGranted('ROLE_USER')]
class CollaborationRequestController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function requestCollaboration( Request $request , CollaborationRequestService $collaborationRequestService) : Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm( CollaborationRequestForm::class, new CollaborationRequest( $user ) );

        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $collaborationRequest = $form->getData();
            $roleRequested = $collaborationRequest->getRoleRequested();
            if (!in_array($roleRequested, CollaborationRequestRole::cases())) {
                $this->addFlash('error', 'Ce rôle n’est pas autorisé.');
                return $this->redirectToRoute('app_request_collaboration');
            }

            try {
                $collaborationRequestService->createCollaborationRequest( $collaborationRequest );
                $this->addFlash('success', 'Votre demande de collaboration a bien été envoyée.');
            } catch ( AlreadyExistCollaborationRequestException $e ) {
                $this->addFlash( 'error', 'Vous avez déjà envoyé une demande de collaboration pour ce rôle.' );
            } catch ( InvalidRoleRequestException $e ) {
                $this->addFlash( 'error', 'Vous ne pouvez pas demander ce rôle.' );
            } catch ( \Exception $e ) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de la demande de collaboration.');
            }

            return $this->redirectBack('app_request_collaboration');
        }

        return $this->render('pages/public/collaboration-request/index.html.twig', [
            'form' => $form->createView(),
        ] );
    }
}
