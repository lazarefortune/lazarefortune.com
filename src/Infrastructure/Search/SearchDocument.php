<?php

namespace App\Infrastructure\Search;

/**
 * Search document
 */
class SearchDocument
{
    public string $title;

    public string $content;

    /**
     * @var string[]
     */
    public array $category;

    public string $type;

    public int $created_at;
}