<?php

namespace App\Http\Controller\Author;

use App\Domain\Auth\Core\Entity\User;
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
    public function requestCollaboration( Request $request ) : Response
    {
        $user = $this->getUser();

        [$form, $response] = $this->createContactForm( $request, $user );

        if ( $response ) {
            return $response;
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