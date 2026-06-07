<?php

declare(strict_types=1);

namespace App\Shared\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DesignSystemController extends AbstractController
{
    #[Route('/design-system', name: 'app_design_system', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('public/design_system.html.twig');
    }
}
