<?php

namespace App\Infrastructure\Search;

interface SearchInterface
{
    public function search( string $query, array $types = [], int $limit = 50, int $page = 1 ) : SearchResult;
}