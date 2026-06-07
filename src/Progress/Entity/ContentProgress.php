<?php

declare(strict_types=1);

namespace App\Progress\Entity;

use App\Auth\Entity\User;
use App\Content\Entity\Content;
use App\Progress\Repository\ContentProgressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentProgressRepository::class)]
#[ORM\Table(name: 'content_progress')]
#[ORM\UniqueConstraint(name: 'uniq_user_content_progress', columns: ['user_id', 'content_id'])]
#[ORM\Index(columns: ['user_id', 'updated_at'], name: 'idx_progress_user_updated_at')]
#[ORM\HasLifecycleCallbacks]
class ContentProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Content $content;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $percent = 0;

    #[ORM\Column(nullable: true)]
    private ?int $lastPositionSeconds = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user, Content $content)
    {
        $this->user = $user;
        $this->content = $content;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getPercent(): int
    {
        return $this->percent;
    }

    public function setPercent(int $percent): static
    {
        $this->percent = $percent;

        return $this;
    }

    public function getLastPositionSeconds(): ?int
    {
        return $this->lastPositionSeconds;
    }

    public function setLastPositionSeconds(?int $lastPositionSeconds): static
    {
        $this->lastPositionSeconds = $lastPositionSeconds;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
