<?php

namespace App\Http\Api\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class AbstractFileManagerController extends AbstractController
{
    protected Finder $finder;

    public function __construct()
    {
        $this->finder = new Finder();
    }

    protected function toRelativePath(string $absolutePath): string
    {
        $filesystem = new Filesystem();
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        return $filesystem->makePathRelative($absolutePath, $uploadsDir);
    }
}