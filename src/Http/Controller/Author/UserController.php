<?php

namespace App\Http\Controller\Author;

use App\Domain\Auth\Core\Entity\User;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'author')]
class UserController extends AbstractController
{
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function showAuthor( User $user ) : Response
    {
        return $this->render('pages/public/users/show.html.twig', [
            'user' => $user,
        ]);
    }
}