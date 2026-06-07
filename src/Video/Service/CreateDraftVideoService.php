<?php

declare(strict_types=1);

namespace App\Video\Service;

use App\Auth\Entity\User;
use App\Content\Repository\ContentRepository;
use App\Video\Dto\CreateDraftVideoInput;
use App\Video\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CreateDraftVideoService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentRepository $contentRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function create(User $author, CreateDraftVideoInput $input): Video
    {
        $title = trim($input->getTitle());
        if ($title === '') {
            throw new \InvalidArgumentException('Le titre est obligatoire.');
        }

        $slug = $this->resolveUniqueSlug($input->getSlug(), $title);

        $video = (new Video($author))
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt($this->normalizeNullableText($input->getExcerpt()))
            ->setLevel($input->getLevel())
            ->setCoverImagePath($this->normalizeNullableText($input->getCoverImagePath()));

        $this->entityManager->persist($video);
        $this->entityManager->flush();

        return $video;
    }

    private function resolveUniqueSlug(?string $rawSlug, string $title): string
    {
        $source = $rawSlug !== null && trim($rawSlug) !== '' ? $rawSlug : $title;
        $baseSlug = $this->normalizeSlug($source);

        if ($baseSlug === '') {
            throw new \InvalidArgumentException('Impossible de générer un slug valide.');
        }

        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->contentRepository->findOneBySlug($candidate) !== null) {
            $candidate = sprintf('%s-%d', $baseSlug, $suffix);
            ++$suffix;
        }

        return $candidate;
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
