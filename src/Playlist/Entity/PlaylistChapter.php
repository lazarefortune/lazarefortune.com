<?php

declare(strict_types=1);

namespace App\Playlist\Entity;

use App\Playlist\Repository\PlaylistChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistChapterRepository::class)]
#[ORM\Table(name: 'playlist_chapters')]
#[ORM\UniqueConstraint(name: 'uniq_playlist_chapter_position', columns: ['playlist_id', 'position'])]
#[ORM\HasLifecycleCallbacks]
class PlaylistChapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Playlist $playlist;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, PlaylistItem> */
    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: PlaylistItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct(Playlist $playlist, string $title, int $position)
    {
        $this->playlist = $playlist;
        $this->title = $title;
        $this->position = $position;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->items = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaylist(): Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(Playlist $playlist): static
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, PlaylistItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PlaylistItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setChapter($this);
        }

        return $this;
    }

    public function removeItem(PlaylistItem $item): static
    {
        $this->items->removeElement($item);

        return $this;
    }
}
