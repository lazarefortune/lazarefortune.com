<?php

namespace App\Http\Admin\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-manager', name: 'file_manager_')]
class FileManagerController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('pages/admin/file-manager/show.html.twig');
    }
}