<?php

declare(strict_types=1);

namespace App\Content\Entity;

use App\Auth\Entity\User;
use App\Content\Enum\ContentVisibility;
use App\Content\Enum\PublicationStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait PublishableTrait
{
    #[ORM\Column(enumType: PublicationStatus::class)]
    private PublicationStatus $status = PublicationStatus::DRAFT;

    #[ORM\Column(enumType: ContentVisibility::class)]
    private ContentVisibility $visibility = ContentVisibility::PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function getStatus(): PublicationStatus
    {
        return $this->status;
    }

    public function setStatus(PublicationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVisibility(): ContentVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(ContentVisibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function isPubliclyVisible(?User $viewer): bool
    {
        if ($this->status !== PublicationStatus::PUBLISHED) {
            return false;
        }

        return match ($this->visibility) {
            ContentVisibility::PUBLIC => true,
            ContentVisibility::MEMBERS_ONLY => $viewer !== null,
        };
    }
}
