<?php

namespace App\Http\Api\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('', name: 'api_')]
class CsrfController extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/csrf-token', name: 'csrf_token', methods: ['GET'])]
    public function getCsrfToken(): JsonResponse
    {
        $token = $this->csrfTokenManager->getToken('quiz_submission');

        return $this->json([
            'token' => $token->getValue()
        ]);
    }
}
