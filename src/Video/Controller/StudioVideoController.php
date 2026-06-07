<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Auth\Entity\User;
use App\Video\Entity\Video;
use App\Video\Dto\CreateDraftVideoInput;
use App\Video\Dto\UpdateVideoContentInput;
use App\Video\Form\CreateDraftVideoType;
use App\Video\Form\UpdateVideoContentType;
use App\Video\Repository\VideoRepository;
use App\Video\Service\CreateDraftVideoService;
use App\Video\Service\UpdateVideoContentService;
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

    #[Route('/videos/{id}/edit', name: 'studio_video_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, UpdateVideoContentService $updateVideoContentService): Response
    {
        $video = $this->videoRepository->find($id);
        if (!$video instanceof Video) {
            throw $this->createNotFoundException();
        }

        $input = $this->buildContentInputFromVideo($video);
        $form = $this->createForm(UpdateVideoContentType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $video = $updateVideoContentService->update($video, $input);
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('studio/video/edit.html.twig', [
                    'video' => $video,
                    'contentForm' => $form,
                ]);
            }

            $this->addFlash('success', sprintf('Le contenu de « %s » a été enregistré.', $video->getTitle()));

            return $this->redirectToRoute('studio_video_edit', [
                'id' => $video->getId(),
                '_fragment' => 'content',
            ]);
        }

        return $this->render('studio/video/edit.html.twig', [
            'video' => $video,
            'contentForm' => $form,
        ]);
    }

    private function buildContentInputFromVideo(Video $video): UpdateVideoContentInput
    {
        return (new UpdateVideoContentInput())
            ->setTitle($video->getTitle())
            ->setSlug($video->getSlug())
            ->setExcerpt($video->getExcerpt())
            ->setDescription($video->getDescription())
            ->setLevel($video->getLevel())
            ->setCoverImagePath($video->getCoverImagePath());
    }
}
