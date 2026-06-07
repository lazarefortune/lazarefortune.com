<?php

declare(strict_types=1);

namespace App\Content\Entity;

use App\Content\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'articles')]
class Article extends Content
{
    #[ORM\Column(type: Types::TEXT)]
    private string $body = '';

    #[ORM\Column(nullable: true)]
    private ?int $readingTimeMinutes = null;

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getReadingTimeMinutes(): ?int
    {
        return $this->readingTimeMinutes;
    }

    public function setReadingTimeMinutes(?int $readingTimeMinutes): static
    {
        $this->readingTimeMinutes = $readingTimeMinutes;

        return $this;
    }
}
