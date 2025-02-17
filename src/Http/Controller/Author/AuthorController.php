<?php

namespace App\Http\Controller\Author;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Domain\Collaboration\Enum\CollaborationRequestRole;
use App\Domain\Collaboration\Exception\AlreadyExistCollaborationRequestException;
use App\Domain\Collaboration\Exception\InvalidRoleRequestException;
use App\Domain\Collaboration\Form\CollaborationRequestForm;
use App\Domain\Collaboration\Service\CollaborationRequestService;
use App\Domain\Contact\ContactService;
use App\Domain\Contact\Dto\ContactData;
use App\Domain\Contact\Entity\Contact;
use App\Domain\Contact\Form\ContactForm;
use App\Domain\Course\Repository\CourseRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auteur', name: 'author_')]
class AuthorController extends AbstractController
{
    public function __construct(
        public readonly  CourseRepository $courseRepository,
        private readonly ContactService $contactService,
    )
    {
    }

    #[Route('/{id}/profile', name: 'show', methods: ['GET'])]
    public function showAuthor( User $user ) : Response
    {
        if ( !$this->hasRole($user, 'ROLE_AUTHOR') ) {
            // redirect to 404
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        $courses = $this->courseRepository->findBy(['author' => $user], ['publishedAt' => 'DESC']);

        return $this->render('pages/public/author/show.html.twig', [
            'author' => $user,
            'courses' => $courses,
        ]);
    }

    #[Route('/demande-de-collaboration', name: 'request_collaboration', methods: ['GET', 'POST'])]
    public function requestCollaboration( Request $request , CollaborationRequestService $collaborationRequestService) : Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

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


        return $this->render('pages/public/author/request_collaboration.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    private function createContactForm( Request $request, User $user = null ) : array
    {
        $contact = new Contact();
        if ( $user ) {
            $contact->setEmail( $user->getEmail() );
            $contact->setName( $user->getFullname() );
        }

        $form = $this->createForm( ContactForm::class, new ContactData( $contact ) );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $data = $form->getData();
            try {
                $this->contactService->sendContactMessage( $data );
                $this->addFlash( 'success', 'Votre message a bien été envoyé.' );
            } catch ( \Exception $e ) {
                $this->addFlash( 'error', 'Une erreur est survenue lors de l\'envoi du message.' );
            }

            return [$form, $this->redirectToRoute( 'app_contact' )];
        }

        return [$form, null];
    }
}