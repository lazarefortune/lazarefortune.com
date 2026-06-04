<?php

namespace App\Infrastructure\Search;

interface SearchInterface
{
    /**
     * @param array<string, mixed> $filters              Supported keys: author_id (int), online (bool), technology_slugs (string)
     * @param string[]             $attributesToSearchOn   Restrict search to these Meilisearch attributes (empty = index defaults)
     */
    public function search(string $query, array $types = [], int $limit = 50, int $page = 1, array $filters = [], array $attributesToSearchOn = []): SearchResult;
}
