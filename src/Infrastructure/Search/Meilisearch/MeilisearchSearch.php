<?php

namespace App\Infrastructure\Search\Meilisearch;

use App\Infrastructure\Search\SearchInterface;
use App\Infrastructure\Search\SearchResult;

class MeilisearchSearch implements SearchInterface
{
    public function __construct(private readonly MeilisearchClient $client)
    {
    }

    public function search(string $q, array $types = [], int $limit = 50, int $page = 1, array $filters = [], array $attributesToSearchOn = []): SearchResult
    {
        $body = [
            'q' => $q,
            'page' => $page,
            'sort' => ['created_at:desc'],
            'attributesToHighlight' => ['title', 'content'],
            'attributesToCrop' => ['content'],
            'cropLength' => 35,
            'hitsPerPage' => $limit,
        ];

        if ([] !== $attributesToSearchOn) {
            $body['attributesToSearchOn'] = $attributesToSearchOn;
        }

        $filter = $this->buildFilter($types, $filters);
        if (null !== $filter) {
            $body['filter'] = [$filter];
        }

        $response = $this->client->post('indexes/content/search', $body);
        $items = $response['hits'];

        return new SearchResult(array_map(fn (array $item) => new MeilisearchItem($item), $items), $response['totalHits'] ?? $response['estimatedTotalHits']);
    }

    /**
     * @param array<string>        $types
     * @param array<string, mixed> $filters
     */
    private function buildFilter(array $types, array $filters): ?string
    {
        $expressions = [];

        if (!empty($types)) {
            $typeFilters = array_map(static fn (string $type) => "type = '$type'", $types);
            $expressions[] = 1 === count($typeFilters)
                ? $typeFilters[0]
                : '('.implode(' OR ', $typeFilters).')';
        }

        if (isset($filters['author_id'])) {
            $expressions[] = 'author_id = '.(int) $filters['author_id'];
        }

        if (array_key_exists('online', $filters)) {
            $expressions[] = $filters['online'] ? 'online = true' : 'online = false';
        }

        if (!empty($filters['technology_slugs'])) {
            $slug = str_replace('"', '\\"', (string) $filters['technology_slugs']);
            $expressions[] = 'technology_slugs = "'.$slug.'"';
        }

        if ([] === $expressions) {
            return null;
        }

        return implode(' AND ', $expressions);
    }
}
