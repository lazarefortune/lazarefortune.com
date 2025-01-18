<?php

namespace App\Domain\Search\Service;

use App\Domain\Application\Repository\ContentRepository;

class SearchService
{
    public function __construct(private readonly ContentRepository $contentRepository)
    {
    }

    public function search( string $query ): array
    {
        return $this->contentRepository->search($query) ?? [];
    }
}