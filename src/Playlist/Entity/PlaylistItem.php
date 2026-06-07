<?php

declare(strict_types=1);

namespace App\Playlist\Entity;

use App\Content\Entity\Content;
use App\Playlist\Repository\PlaylistItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistItemRepository::class)]
#[ORM\Table(name: 'playlist_items')]
#[ORM\UniqueConstraint(name: 'uniq_chapter_item_position', columns: ['chapter_id', 'position'])]
#[ORM\UniqueConstraint(name: 'uniq_chapter_content', columns: ['chapter_id', 'content_id'])]
class PlaylistItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PlaylistChapter $chapter;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Content $content;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(PlaylistChapter $chapter, Content $content, int $position)
    {
        $this->chapter = $chapter;
        $this->content = $content;
        $this->position = $position;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChapter(): PlaylistChapter
    {
        return $this->chapter;
    }

    public function setChapter(PlaylistChapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
