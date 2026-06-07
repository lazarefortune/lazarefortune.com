<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Auth\Entity\User;
use App\Video\Entity\Video;
use App\Video\Dto\CreateDraftVideoInput;
use App\Video\Dto\UpdateVideoContentInput;
use App\Video\Exception\InvalidPublicationScheduleException;
use App\Video\Form\CreateDraftVideoType;
use App\Video\Form\UpdateVideoContentType;
use App\Video\Repository\VideoRepository;
use App\Video\Service\CreateDraftVideoService;
use App\Video\Service\UpdateVideoContentService;
use App\Video\Service\VideoPublicationService;
use App\Video\Presenter\VideoPublicationActionsPresenter;
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
    public function edit(
        int $id,
        Request $request,
        UpdateVideoContentService $updateVideoContentService,
        VideoPublicationActionsPresenter $publicationActionsPresenter,
    ): Response {
        $video = $this->findVideoOr404($id);

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
                    'publicationActions' => $publicationActionsPresenter->present($video),
                ]);
            }

            $this->addFlash('success', sprintf('Le contenu de « %s » a ete enregistre.', $video->getTitle()));

            return $this->redirectToRoute('studio_video_edit', [
                'id' => $video->getId(),
                '_fragment' => 'content',
            ]);
        }

        return $this->render('studio/video/edit.html.twig', [
            'video' => $video,
            'contentForm' => $form,
            'publicationActions' => $publicationActionsPresenter->present($video),
        ]);
    }

    #[Route('/videos/{id}/publish', name: 'studio_video_publish', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function publish(int $id, Request $request, VideoPublicationService $videoPublicationService): Response
    {
        $video = $this->findVideoOr404($id);
        $this->denyUnlessValidCsrf($request, 'studio_video_publish');

        $videoPublicationService->publishNow($video);
        $this->addFlash('success', sprintf('« %s » est maintenant publiee.', $video->getTitle()));

        return $this->redirectToPublicationTab($video);
    }

    #[Route('/videos/{id}/schedule', name: 'studio_video_schedule', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function schedule(int $id, Request $request, VideoPublicationService $videoPublicationService): Response
    {
        $video = $this->findVideoOr404($id);
        $this->denyUnlessValidCsrf($request, 'studio_video_schedule');

        $scheduledAtRaw = trim($request->request->getString('scheduled_at'));
        if ($scheduledAtRaw === '') {
            $this->addFlash('error', 'La date de publication est obligatoire.');

            return $this->redirectToPublicationTab($video);
        }

        try {
            $scheduledAt = new \DateTimeImmutable($scheduledAtRaw);
            $videoPublicationService->schedule($video, $scheduledAt);
        } catch (\Exception $exception) {
            $message = $exception instanceof InvalidPublicationScheduleException
                ? $exception->getMessage()
                : 'Date de publication invalide.';
            $this->addFlash('error', $message);

            return $this->redirectToPublicationTab($video);
        }

        $this->addFlash(
            'success',
            sprintf('« %s » sera publiee le %s.', $video->getTitle(), $scheduledAt->format('d/m/Y H:i')),
        );

        return $this->redirectToPublicationTab($video);
    }

    #[Route('/videos/{id}/draft', name: 'studio_video_draft', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function draft(int $id, Request $request, VideoPublicationService $videoPublicationService): Response
    {
        $video = $this->findVideoOr404($id);
        $this->denyUnlessValidCsrf($request, 'studio_video_draft');

        $videoPublicationService->markAsDraft($video);
        $this->addFlash('success', sprintf('« %s » a ete remise en brouillon.', $video->getTitle()));

        return $this->redirectToPublicationTab($video);
    }

    #[Route('/videos/{id}/archive', name: 'studio_video_archive', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function archive(int $id, Request $request, VideoPublicationService $videoPublicationService): Response
    {
        $video = $this->findVideoOr404($id);
        $this->denyUnlessValidCsrf($request, 'studio_video_archive');

        $videoPublicationService->archive($video);
        $this->addFlash('success', sprintf('« %s » a ete archivee.', $video->getTitle()));

        return $this->redirectToPublicationTab($video);
    }

    private function findVideoOr404(int $id): Video
    {
        $video = $this->videoRepository->find($id);
        if (!$video instanceof Video) {
            throw $this->createNotFoundException();
        }

        return $video;
    }

    private function denyUnlessValidCsrf(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }

    private function redirectToPublicationTab(Video $video): Response
    {
        return $this->redirectToRoute('studio_video_edit', [
            'id' => $video->getId(),
            '_fragment' => 'publication',
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
