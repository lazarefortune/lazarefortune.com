<?php

declare(strict_types=1);

namespace App\Video\Dto;

use App\Video\Enum\VideoProvider;
use App\Video\Enum\VideoVisibility;

final class UpdateVideoSourceInput
{
    private string $sourceRef = '';

    private VideoProvider $provider = VideoProvider::YOUTUBE;

    private VideoVisibility $visibility = VideoVisibility::PRIVATE;

    private ?string $thumbnailUrl = null;

    private ?int $durationSeconds = null;

    public function getSourceRef(): string
    {
        return $this->sourceRef;
    }

    public function setSourceRef(string $sourceRef): self
    {
        $this->sourceRef = $sourceRef;

        return $this;
    }

    public function getProvider(): VideoProvider
    {
        return $this->provider;
    }

    public function setProvider(VideoProvider $provider): self
    {
        $this->provider = $provider;

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

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): self
    {
        $this->thumbnailUrl = $thumbnailUrl;

        return $this;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): self
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }
}
