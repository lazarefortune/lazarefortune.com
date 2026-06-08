<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Video\Entity\Video;
use App\Video\Dto\UpdateVideoContentInput;
use App\Video\Exception\InvalidPublicationScheduleException;
use App\Video\Form\UpdateVideoContentType;
use App\Video\Repository\VideoRepository;
use App\Video\Service\UpdateVideoContentService;
use App\Video\Service\VideoPublicationService;
use App\Video\Presenter\VideoPublicationActionsPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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

    #[Route('/videos/new', name: 'studio_video_new', methods: ['GET'])]
    public function new(CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        return $this->render('studio/video/new.html.twig', [
            'video_create_config' => [
                'createUrl' => $this->generateUrl('studio_api_video_create'),
                'csrfToken' => $csrfTokenManager->getToken('studio_api_video_create')->getValue(),
                'indexUrl' => $this->generateUrl('studio_video_index'),
            ],
        ]);
    }

    #[Route('/videos/{id}/edit', name: 'studio_video_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        UpdateVideoContentService $updateVideoContentService,
        VideoPublicationActionsPresenter $publicationActionsPresenter,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        $video = $this->findVideoOr404($id);

        $contentInput = $this->buildContentInputFromVideo($video);
        $contentForm = $this->createForm(UpdateVideoContentType::class, $contentInput);

        $contentForm->handleRequest($request);

        if ($contentForm->isSubmitted() && $contentForm->isValid()) {
            try {
                $video = $updateVideoContentService->update($video, $contentInput);
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->renderEditPage($video, $contentForm, $publicationActionsPresenter, $csrfTokenManager);
            }

            $this->addFlash('success', sprintf('Le contenu de « %s » a ete enregistre.', $video->getTitle()));

            return $this->redirectToRoute('studio_video_edit', [
                'id' => $video->getId(),
                '_fragment' => 'content',
            ]);
        }

        return $this->renderEditPage($video, $contentForm, $publicationActionsPresenter, $csrfTokenManager);
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

    private function renderEditPage(
        Video $video,
        FormInterface $contentForm,
        VideoPublicationActionsPresenter $publicationActionsPresenter,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        return $this->render('studio/video/edit.html.twig', [
            'video' => $video,
            'contentForm' => $contentForm,
            'publicationActions' => $publicationActionsPresenter->present($video),
            'video_source_config' => $this->buildVideoSourceConfig($video, $csrfTokenManager),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildVideoSourceConfig(Video $video, CsrfTokenManagerInterface $csrfTokenManager): array
    {
        $videoId = $video->getId();
        if ($videoId === null) {
            throw new \RuntimeException('La video n\'a pas d\'identifiant.');
        }

        $primarySource = $video->getPrimarySource();

        return [
            'videoId' => $videoId,
            'updateSourceUrl' => $this->generateUrl('studio_api_video_source_update', ['id' => $videoId]),
            'csrfToken' => $csrfTokenManager->getToken('studio_api_video_source_update')->getValue(),
            'initialSource' => $primarySource !== null ? [
                'provider' => $primarySource->getProvider()->value,
                'externalId' => $primarySource->getExternalId(),
                'url' => $primarySource->getUrl(),
                'visibility' => $primarySource->getVisibility()->value,
            ] : null,
            'availableProviders' => [
                ['id' => 'youtube', 'label' => 'YouTube', 'available' => true],
                ['id' => 'vimeo', 'label' => 'Vimeo', 'available' => false],
                ['id' => 'self_hosted', 'label' => 'Auto-heberge', 'available' => false],
            ],
        ];
    }
}
