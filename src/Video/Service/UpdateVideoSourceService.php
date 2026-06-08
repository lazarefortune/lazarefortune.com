<?php

declare(strict_types=1);

namespace App\Video\Service;

use App\Video\Dto\UpdateVideoSourceInput;
use App\Video\Entity\Video;
use App\Video\Entity\VideoSource;
use App\Video\Enum\VideoProvider;
use App\Video\Repository\VideoSourceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateVideoSourceService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VideoSourceRepository $videoSourceRepository,
        private readonly YoutubeVideoIdExtractor $youtubeVideoIdExtractor,
    ) {
    }

    public function update(Video $video, UpdateVideoSourceInput $input): VideoSource
    {
        $sourceRef = trim($input->getSourceRef());
        if ($sourceRef === '') {
            throw new \InvalidArgumentException('La reference source est obligatoire.');
        }

        $provider = $input->getProvider();
        if ($provider !== VideoProvider::YOUTUBE) {
            throw new \InvalidArgumentException('Seule une source YouTube manuelle est disponible pour le moment.');
        }

        $externalId = $this->youtubeVideoIdExtractor->extract($sourceRef);
        if ($externalId === null) {
            throw new \InvalidArgumentException('URL ou identifiant YouTube invalide.');
        }

        $this->assertExternalIdAvailableForVideo($provider, $externalId, $video);

        $source = $video->getPrimarySource();
        if (!$source instanceof VideoSource) {
            $source = new VideoSource($video, $provider);
            $video->addSource($source);
        } else {
            $source->setProvider($provider);
        }

        $source
            ->setExternalId($externalId)
            ->setUrl($this->resolveStoredUrl($sourceRef, $externalId))
            ->setVisibility($input->getVisibility())
            ->setIsPrimary(true)
            ->setThumbnailUrl($this->normalizeNullableText($input->getThumbnailUrl()))
            ->setDurationSeconds($input->getDurationSeconds());

        foreach ($video->getSources() as $otherSource) {
            if ($otherSource !== $source && $otherSource->isPrimary()) {
                $otherSource->setIsPrimary(false);
            }
        }

        $this->entityManager->flush();

        return $source;
    }

    private function assertExternalIdAvailableForVideo(
        VideoProvider $provider,
        string $externalId,
        Video $video,
    ): void {
        $existing = $this->videoSourceRepository->findOneBy([
            'provider' => $provider,
            'externalId' => $externalId,
        ]);

        if (!$existing instanceof VideoSource) {
            return;
        }

        if ($existing->getVideo()->getId() !== $video->getId()) {
            throw new \InvalidArgumentException('Cette source YouTube est deja utilisee par une autre video.');
        }
    }

    private function resolveStoredUrl(string $sourceRef, string $externalId): string
    {
        if (filter_var($sourceRef, FILTER_VALIDATE_URL) !== false) {
            return trim($sourceRef);
        }

        return sprintf('https://www.youtube.com/watch?v=%s', $externalId);
    }

    private function normalizeNullableText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
