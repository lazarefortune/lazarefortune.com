<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home() : Response
    {
        return $this->render('home/index.html.twig');
    }
    #[Route('/ui', name: 'app_ui', methods: ['GET'])]
    public function ui() : Response
    {
        return $this->render('home/ui.html.twig');
    }
}
