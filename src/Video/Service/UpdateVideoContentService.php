<?php

declare(strict_types=1);

namespace App\Video\Service;

use App\Content\Entity\Content;
use App\Content\Repository\ContentRepository;
use App\Video\Dto\UpdateVideoContentInput;
use App\Video\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UpdateVideoContentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentRepository $contentRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function update(Video $video, UpdateVideoContentInput $input): Video
    {
        $title = trim($input->getTitle());
        if ($title === '') {
            throw new \InvalidArgumentException('Le titre est obligatoire.');
        }

        $contentId = $video->getId();
        if ($contentId === null) {
            throw new \InvalidArgumentException('Impossible de modifier une video sans identifiant.');
        }

        $slug = $this->resolveUniqueSlug(
            $input->getSlug(),
            $title,
            $video->getSlug(),
            $contentId,
        );

        $video
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt($this->normalizeNullableText($input->getExcerpt()))
            ->setDescription(trim($input->getDescription()))
            ->setLevel($input->getLevel())
            ->setCoverImagePath($this->normalizeNullableText($input->getCoverImagePath()));

        $this->entityManager->flush();

        return $video;
    }

    private function resolveUniqueSlug(
        ?string $rawSlug,
        string $title,
        string $currentSlug,
        int $contentId,
    ): string {
        $source = $rawSlug !== null && trim($rawSlug) !== '' ? $rawSlug : $title;
        $baseSlug = $this->normalizeSlug($source);

        if ($baseSlug === '') {
            throw new \InvalidArgumentException('Impossible de generer un slug valide.');
        }

        if ($baseSlug === $currentSlug) {
            return $currentSlug;
        }

        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->isSlugTakenByOtherContent($candidate, $contentId)) {
            $candidate = sprintf('%s-%d', $baseSlug, $suffix);
            ++$suffix;
        }

        return $candidate;
    }

    private function isSlugTakenByOtherContent(string $slug, int $contentId): bool
    {
        $existing = $this->contentRepository->findOneBySlug($slug);
        if (!$existing instanceof Content) {
            return false;
        }

        return $existing->getId() !== $contentId;
    }

    private function normalizeSlug(string $value): string
    {
        return strtolower($this->slugger->slug(trim($value))->toString());
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
