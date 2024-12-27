<?php

namespace App\Http\Controller\Author;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Repository\CourseRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auteur', name: 'author_')]
class AuthorController extends AbstractController
{
    public function __construct(
        public readonly  CourseRepository $courseRepository
    )
    {
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
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
}