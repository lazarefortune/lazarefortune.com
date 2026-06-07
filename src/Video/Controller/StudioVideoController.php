<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Video\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/studio')]
final class StudioVideoController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
    }

    #[Route('/videos', name: 'studio_video_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('studio/video/index.html.twig', [
            'videos' => $this->videoRepository->findLatestForStudio(),
        ]);
    }
}
