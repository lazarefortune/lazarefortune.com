<?php

namespace App\Infrastructure\Search\Meilisearch;

use App\Infrastructure\Search\IndexerInterface;

class MeilisearchIndexer implements IndexerInterface
{
    public function __construct(private readonly MeilisearchClient $client)
    {
    }

    public function settings(): void
    {
        $this->client->patch('indexes/content/settings', [
            'minWordSizeForTypos' => [
                'oneTypo' => 2, // autoriser une faute dès 2 caractères
                'twoTypos' => 4, // autoriser 2 fautes dès 4 caractères
            ],
            'searchableAttributes' => [
                'title',
                'category',
                'content',
                'url',
            ],
            'sortableAttributes' => ['created_at'],
            'filterableAttributes' => ['type'],
        ]);
    }

    public function index(array $data): void
    {
        $this->client->put('indexes/content/documents', [$data]);
    }

    public function remove(string $id): void
    {
        $this->client->delete("indexes/content/documents/{$id}");
    }

    public function clean(): void
    {
        $this->client->delete('indexes/content/documents');
    }
}