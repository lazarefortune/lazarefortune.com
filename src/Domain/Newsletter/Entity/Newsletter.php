<?php

namespace App\Domain\Newsletter\Entity;

use App\Domain\Newsletter\Enum\NewsletterStatus;
use App\Domain\Newsletter\Enum\NewsletterTargetGroupOptions;
use App\Domain\Newsletter\Repository\NewsletterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
#[ORM\Table(name: 'newsletter')]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sendAt = null;

    #[ORM\Column(length: 50)]
    private NewsletterStatus $status = NewsletterStatus::PENDING;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDraft = false;

    #[ORM\Column(length: 20)]
    private NewsletterTargetGroupOptions $targetGroup = NewsletterTargetGroupOptions::ALL;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getSendAt(): ?\DateTimeInterface
    {
        return $this->sendAt;
    }

    public function setSendAt(?\DateTimeInterface $sendAt): self
    {
        $this->sendAt = $sendAt;
        return $this;
    }

    public function getStatus(): NewsletterStatus
    {
        return $this->status;
    }

    public function setStatus(NewsletterStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): self
    {
        $this->isDraft = $isDraft;
        return $this;
    }

    public function getTargetGroup(): NewsletterTargetGroupOptions
    {
        return $this->targetGroup;
    }

    public function setTargetGroup(NewsletterTargetGroupOptions $targetGroup): self
    {
        $this->targetGroup = $targetGroup;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isSent(): bool
    {
        return $this->status == NewsletterStatus::SENT;
    }
}