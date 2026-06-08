<?php

declare(strict_types=1);

namespace App\Video\Dto;

use App\Video\Enum\VideoVisibility;

final class CreateStudioVideoApiInput
{
    public const MODE_YOUTUBE_EXISTING = 'youtube_existing';

    public const MODE_IDEA = 'idea';

    private string $mode = '';

    private string $title = '';

    private string $sourceRef = '';

    private VideoVisibility $visibility = VideoVisibility::UNLISTED;

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSourceRef(): string
    {
        return $this->sourceRef;
    }

    public function setSourceRef(string $sourceRef): self
    {
        $this->sourceRef = $sourceRef;

        return $this;
    }

    public function getVisibility(): VideoVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(VideoVisibility $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
