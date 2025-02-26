<?php

namespace App\Domain\Application\Event;

use App\Domain\Application\Entity\Content;

class ContentDeletedEvent
{
    const NAME = 'content.deleted';

    public function __construct(private readonly Content $content)
    {
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}