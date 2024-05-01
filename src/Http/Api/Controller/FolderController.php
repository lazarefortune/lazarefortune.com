<?php

namespace App\Http\Api\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FolderController extends AbstractFileManagerController
{

    #[Route(path: '/folders', name: 'list_folders', methods: ['GET'])]
    public function listFolders(): JsonResponse
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $this->finder->directories()->in($uploadDir);

        $folders = array_map([$this, 'mapFolderDetails'], iterator_to_array($this->finder, false));
        return $this->json($folders);
    }

    #[Route('/folders', name: 'app_post_folder', methods: ['POST'])]
    public function store(Request $request)
    {
        $parent = json_decode($request->getContent(), true)['parent'];
        $dir = json_decode($request->getContent(), true)['name'];
        $path = ($parent ?? '') . '/' . $dir;
        $filesystem = new Filesystem();
        $filesystem->mkdir($path);
        return $this->json($this->toArray($path));
    }

    #[Route('/folders/{folder}', name: 'app_delete_folder', requirements: ['folder' => '.*'], methods: ['DELETE'] )]
    public function delete(string $folder): Response
    {
        $filesystem = new Filesystem();
        $filesystem->remove($folder);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    public function toArray(string $folder): array
    {
        $pathinfo = pathinfo($folder);
        $filesystem = new Filesystem();
        $parent = $filesystem->makePathRelative(
            $pathinfo['dirname'],
            $this->getParameter('kernel.project_dir').'/public/uploads/'
        );
        $parent = rtrim($parent, '/');
        return [
            'id' => $parent === '.' ?  $pathinfo['filename'] : $parent . '/' . $pathinfo['filename'],
            'name' => $pathinfo['filename'],
            'parent' => $parent === '.' ? null : $parent
        ];
    }

    private function mapFolderDetails($folder): array
    {
        $relativePath = $this->toRelativePath($folder->getRealPath());
        return [
            'id' => trim($relativePath, '/'),
            'name' => $folder->getFilename(),
            'parent' => dirname($relativePath) === '.' ? null : trim(dirname($relativePath), '/')
        ];
    }
}