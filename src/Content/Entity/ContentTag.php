<?php

declare(strict_types=1);

namespace App\Content\Entity;

use App\Tag\Entity\Tag;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'content_tags')]
#[ORM\UniqueConstraint(name: 'uniq_content_tag', columns: ['content_id', 'tag_id'])]
class ContentTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contentTags')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Content $content;

    #[ORM\ManyToOne(inversedBy: 'contentTags')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Tag $tag;

    #[ORM\Column]
    private bool $isPrimary = false;

    public function __construct(Content $content, Tag $tag, bool $isPrimary = false)
    {
        $this->content = $content;
        $this->tag = $tag;
        $this->isPrimary = $isPrimary;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function setContent(Content $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): static
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }
}
