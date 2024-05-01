<?php

namespace App\Http\Api\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileController extends AbstractFileManagerController
{
    #[Route(path: '/files', name: 'list_files', methods: ['GET'])]
    public function listFiles(Request $request): JsonResponse
    {
        $folderQuery = $request->query->get('folder', '');
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $folderQuery;

        $this->finder->files()->in($uploadDir)->depth(0);

        $files = array_map([$this, 'mapFileDetails'], iterator_to_array($this->finder, false));
        return $this->json($files);
    }

    private function mapFileDetails($file): array
    {
        $relativePath = $this->toRelativePath($file->getRealPath());
        $relativePath = rtrim($relativePath, '/');

        return [
            'id' => $file->getRealPath(),
            'name' => $file->getFilename(),
            'url' => "/uploads/" . $relativePath,
            'size' => $file->getSize(),
            'parent' => dirname($relativePath) === '.' ? null : trim(dirname($relativePath), '/'),
            'thumbnail' => "/uploads/" . $relativePath
        ];
    }

    #[Route('/files', name: 'app_post_file', methods: ['POST'])]
    public function store(Request $request, SluggerInterface $slugger) : JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $folder = $request->get('folder');
        $folder = $this->getParameter('kernel.project_dir').'/public/uploads/' . $folder;
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $path = $file->move($folder, $newFilename);
        return $this->json($this->toArray($path));
    }

    #[Route('/files/{file}', name: 'app_delete_file', requirements: ['file' => '.*'], methods: ['DELETE'] )]
    public function delete(string $file, Filesystem $filesystem) : Response
    {
        $filesystem->remove($file);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    public function toArray(string $file): array
    {
        $pathinfo = pathinfo($file);
        $filesystem = new Filesystem();
        $rootDirectory = $filesystem->makePathRelative(
            $pathinfo['dirname'],
            $this->getParameter('kernel.project_dir').'/public/'
        );
        return [
            'id' => $file,
            'name' => $pathinfo['basename'],
            'url' => $rootDirectory . $pathinfo['basename'],
            'size' => filesize($file),
            'folder' => $pathinfo['dirname'] === '.' ? null : $pathinfo['dirname'],
            'thumbnail' => $rootDirectory . $pathinfo['basename'],
        ];
    }

}