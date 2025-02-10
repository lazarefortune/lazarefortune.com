<?php

namespace App\Infrastructure\Search;

class SearchResult
{
    public function __construct( private readonly array $items, private readonly int $total )
    {
    }

    /**
     * Get search result items
     *
     * @return SearchResultItemInterface[] $items
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * Get total number of items
     *
     * @return int
     */
    public function getTotal() : int
    {
        return $this->total;
    }
}