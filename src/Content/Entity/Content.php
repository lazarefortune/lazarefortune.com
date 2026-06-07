<?php

declare(strict_types=1);

namespace App\Content\Entity;

use App\Auth\Entity\User;
use App\Content\Contract\PublishableResource;
use App\Content\Enum\ContentLevel;
use App\Content\Enum\ContentType;
use App\Content\Repository\ContentRepository;
use App\Video\Entity\Video;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\Table(name: 'contents')]
#[ORM\Index(columns: ['status', 'published_at'], name: 'idx_content_status_published_at')]
#[ORM\Index(columns: ['author_id'], name: 'idx_content_author')]
#[ORM\UniqueConstraint(name: 'uniq_content_slug', columns: ['slug'])]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string', enumType: ContentType::class)]
#[ORM\DiscriminatorMap([
    ContentType::VIDEO->value => Video::class,
    ContentType::ARTICLE->value => Article::class,
])]
#[ORM\HasLifecycleCallbacks]
abstract class Content implements PublishableResource
{
    use PublishableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\Column(enumType: ContentLevel::class, nullable: true)]
    private ?ContentLevel $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $coverImagePath = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Content $replacedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ContentTag> */
    #[ORM\OneToMany(mappedBy: 'content', targetEntity: ContentTag::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contentTags;

    public function __construct(User $author)
    {
        $this->author = $author;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->contentTags = new ArrayCollection();
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

    public function getType(): ContentType
    {
        return ContentType::fromClass(static::class);
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getLevel(): ?ContentLevel
    {
        return $this->level;
    }

    public function setLevel(?ContentLevel $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): static
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): static
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    public function getCoverImagePath(): ?string
    {
        return $this->coverImagePath;
    }

    public function setCoverImagePath(?string $coverImagePath): static
    {
        $this->coverImagePath = $coverImagePath;

        return $this;
    }

    public function getReplacedBy(): ?Content
    {
        return $this->replacedBy;
    }

    public function setReplacedBy(?Content $replacedBy): static
    {
        $this->replacedBy = $replacedBy;

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

    /** @return Collection<int, ContentTag> */
    public function getContentTags(): Collection
    {
        return $this->contentTags;
    }

    public function addContentTag(ContentTag $contentTag): static
    {
        if (!$this->contentTags->contains($contentTag)) {
            $this->contentTags->add($contentTag);
            $contentTag->setContent($this);
        }

        return $this;
    }

    public function removeContentTag(ContentTag $contentTag): static
    {
        $this->contentTags->removeElement($contentTag);

        return $this;
    }
}
