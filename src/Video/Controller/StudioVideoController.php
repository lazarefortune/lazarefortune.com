<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Auth\Entity\User;
use App\Video\Entity\Video;
use App\Video\Dto\CreateDraftVideoInput;
use App\Video\Form\CreateDraftVideoType;
use App\Video\Repository\VideoRepository;
use App\Video\Service\CreateDraftVideoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/videos/new', name: 'studio_video_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CreateDraftVideoService $createDraftVideoService): Response
    {
        $input = new CreateDraftVideoInput();
        $form = $this->createForm(CreateDraftVideoType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            try {
                $video = $createDraftVideoService->create($user, $input);
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('studio_video_new');
            }

            $this->addFlash('success', sprintf('Le brouillon « %s » a été créé.', $video->getTitle()));

            return $this->redirectToRoute('studio_video_edit', ['id' => $video->getId()]);
        }

        return $this->render('studio/video/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/videos/{id}/edit', name: 'studio_video_edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $video = $this->videoRepository->find($id);
        if (!$video instanceof Video) {
            throw $this->createNotFoundException();
        }

        return $this->render('studio/video/edit.html.twig', [
            'video' => $video,
        ]);
    }
}
