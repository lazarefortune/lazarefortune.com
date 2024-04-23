<?php

namespace App\Http\Education\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route( '/' , name: 'home' , methods: ['GET'] )]
    public function index() : Response
    {
        return $this->render( 'education/pages/index.html.twig' );
    }
}
