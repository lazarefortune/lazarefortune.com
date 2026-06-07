<?php

declare(strict_types=1);

namespace App\Video\Dto;

use App\Content\Enum\ContentLevel;

final class CreateDraftVideoInput
{
    private string $title = '';

    private ?string $slug = null;

    private ?string $excerpt = null;

    private ?ContentLevel $level = null;

    private ?string $coverImagePath = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getLevel(): ?ContentLevel
    {
        return $this->level;
    }

    public function setLevel(?ContentLevel $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getCoverImagePath(): ?string
    {
        return $this->coverImagePath;
    }

    public function setCoverImagePath(?string $coverImagePath): self
    {
        $this->coverImagePath = $coverImagePath;

        return $this;
    }
}
