<?php

namespace App\Http\Controller\Author;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Repository\CourseRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'author_')]
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
        # check user role
        if ( $this->hasRole($user, 'ROLE_AUTHOR') ) {
            $courses = $this->courseRepository->findBy(['author' => $user], ['publishedAt' => 'DESC']);

            return $this->render('pages/public/author/show.html.twig', [
                'author' => $user,
                'courses' => $courses,
            ]);
        }


        return $this->render('pages/public/users/show.html.twig', [
            'user' => $user,
        ]);
    }
}