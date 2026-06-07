<?php

declare(strict_types=1);

namespace App\Playlist\Controller;

use App\Playlist\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/studio')]
final class StudioPlaylistController extends AbstractController
{
    public function __construct(
        private readonly PlaylistRepository $playlistRepository,
    ) {
    }

    #[Route('/playlists', name: 'studio_playlist_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('studio/playlist/index.html.twig', [
            'playlists' => $this->playlistRepository->findLatestForStudio(),
        ]);
    }
}
