<?php
// src/Http/Api/Controller/UserController.php

namespace App\Http\Api\Controller;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/current_user', name: 'current_user', methods: ['GET'])]
    public function currentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(null, 200);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getFullname(),
            // Ajoutez d'autres informations si nécessaire
        ]);
    }
}
