<?php

declare(strict_types=1);

namespace App\Content\Service;

final readonly class ScheduledPublicationResult
{
    public function __construct(
        public int $publishedContents = 0,
        public int $publishedPlaylists = 0,
    ) {
    }

    public function total(): int
    {
        return $this->publishedContents + $this->publishedPlaylists;
    }
}
