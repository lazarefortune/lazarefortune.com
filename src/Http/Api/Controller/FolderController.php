<?php

namespace App\Http\Api\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

class FolderController extends AbstractController
{
    #[Route( '/folders', name: 'folders', methods: ['GET'] )]
    public function index()
    {
        // get all folders
        $fileSystem = new Filesystem();
        $finder = new Finder();

        $folders = $finder->directories()->in( $this->getParameter( 'kernel.project_dir' ) . '/public' )->files();
        dd( $folders );

    }
}