<?php

namespace App\Studio\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/studio')]
final class StudioController extends AbstractController
{
    #[Route('', name: 'studio_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('studio/index.html.twig');
    }
}
