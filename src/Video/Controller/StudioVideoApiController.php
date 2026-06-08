<?php

declare(strict_types=1);

namespace App\Video\Controller;

use App\Auth\Entity\User;
use App\Video\Dto\CreateStudioVideoApiInput;
use App\Video\Dto\UpdateVideoSourceInput;
use App\Video\Entity\Video;
use App\Video\Entity\VideoSource;
use App\Video\Enum\VideoProvider;
use App\Video\Enum\VideoVisibility;
use App\Video\Repository\VideoRepository;
use App\Video\Service\CreateStudioVideoService;
use App\Video\Service\UpdateVideoSourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/studio/api')]
final class StudioVideoApiController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {
    }

    #[Route('/videos', name: 'studio_api_video_create', methods: ['POST'])]
    public function create(
        Request $request,
        CreateStudioVideoService $createStudioVideoService,
    ): JsonResponse {
        $this->denyUnlessValidCsrf($request, 'studio_api_video_create');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $payload = $this->decodeJsonPayload($request);
            $input = $this->buildInputFromPayload($payload);
            $result = $createStudioVideoService->create($user, $input);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $videoId = $result->getVideo()->getId();
        if ($videoId === null) {
            throw new \RuntimeException('La video creee n\'a pas d\'identifiant.');
        }

        $redirectUrl = $this->generateUrl('studio_video_edit', [
            'id' => $videoId,
            '_fragment' => $result->getRedirectFragment(),
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->json([
            'success' => true,
            'redirectUrl' => $redirectUrl,
        ]);
    }

    #[Route('/videos/{id}/source', name: 'studio_api_video_source_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateSource(
        int $id,
        Request $request,
        UpdateVideoSourceService $updateVideoSourceService,
    ): JsonResponse {
        $this->denyUnlessValidCsrf($request, 'studio_api_video_source_update');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $video = $this->findVideoOr404($id);

        try {
            $payload = $this->decodeJsonPayload($request);
            $input = $this->buildSourceInputFromPayload($payload);
            $source = $updateVideoSourceService->update($video, $input);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'source' => $this->serializeSource($source),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonPayload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            throw $this->createBadRequestException('Corps JSON invalide.');
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildInputFromPayload(array $payload): CreateStudioVideoApiInput
    {
        $mode = is_string($payload['mode'] ?? null) ? trim($payload['mode']) : '';
        $title = is_string($payload['title'] ?? null) ? trim($payload['title']) : '';
        $sourceRef = is_string($payload['sourceRef'] ?? null) ? trim($payload['sourceRef']) : '';
        $visibilityRaw = is_string($payload['visibility'] ?? null) ? trim($payload['visibility']) : '';

        $visibility = VideoVisibility::tryFrom($visibilityRaw) ?? VideoVisibility::UNLISTED;

        return (new CreateStudioVideoApiInput())
            ->setMode($mode)
            ->setTitle($title)
            ->setSourceRef($sourceRef)
            ->setVisibility($visibility);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildSourceInputFromPayload(array $payload): UpdateVideoSourceInput
    {
        $sourceRef = is_string($payload['sourceRef'] ?? null) ? trim($payload['sourceRef']) : '';
        $visibilityRaw = is_string($payload['visibility'] ?? null) ? trim($payload['visibility']) : '';
        $visibility = VideoVisibility::tryFrom($visibilityRaw) ?? VideoVisibility::UNLISTED;

        return (new UpdateVideoSourceInput())
            ->setSourceRef($sourceRef)
            ->setProvider(VideoProvider::YOUTUBE)
            ->setVisibility($visibility);
    }

    /**
     * @return array{provider: string, externalId: string|null, url: string|null, visibility: string}
     */
    private function serializeSource(VideoSource $source): array
    {
        return [
            'provider' => $source->getProvider()->value,
            'externalId' => $source->getExternalId(),
            'url' => $source->getUrl(),
            'visibility' => $source->getVisibility()->value,
        ];
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
        $token = $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }

    private function createBadRequestException(string $message): \InvalidArgumentException
    {
        return new \InvalidArgumentException($message);
    }
}
