<?php

declare(strict_types=1);

namespace App\Video\Dto;

use App\Video\Entity\Video;

final class CreateStudioVideoResult
{
    public function __construct(
        private readonly Video $video,
        private readonly string $redirectFragment,
    ) {
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function getRedirectFragment(): string
    {
        return $this->redirectFragment;
    }
}
