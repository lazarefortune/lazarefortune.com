<?php

namespace App\Domain\Course\Entity;

use App\Domain\Application\Entity\Content;
use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Course\Repository\CourseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable()]
#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course extends Content
{
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $duration = 0;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $youtubeTitle = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $youtubeId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $youtubeUploadUrl = null;

    #[ORM\ManyToOne(targetEntity: Attachment::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'youtube_thumbnail_id', referencedColumnName: 'id')]
    private ?Attachment $youtubeThumbnail = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $videoPath = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $source = null;

    #[Vich\UploadableField(mapping: 'sources', fileNameProperty: 'source')]
    private ?File $sourceFile = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    private ?Course $deprecatedBy = null;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Formation $formation = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isRestrictedToUser = false;

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): self
    {
        $this->youtubeId = $youtubeId;

        return $this;
    }

    public function getYoutubeUploadUrl(): ?string
    {
        return $this->youtubeUploadUrl;
    }

    public function setYoutubeUploadUrl(?string $youtubeUploadUrl): self
    {
        $this->youtubeUploadUrl = $youtubeUploadUrl;

        return $this;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function setVideoPath(?string $videoPath): self
    {
        $this->videoPath = $videoPath;

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceFile(): ?File
    {
        return $this->sourceFile;
    }

    public function setSourceFile(?File $sourceFile): self
    {
        $this->sourceFile = $sourceFile;

        return $this;
    }

    public function getDeprecatedBy(): ?Course
    {
        return $this->deprecatedBy;
    }

    public function setDeprecatedBy(?Course $deprecatedBy): self
    {
        $this->deprecatedBy = $deprecatedBy;

        return $this;
    }

    public function getYoutubeThumbnail(): ?Attachment
    {
        return $this->youtubeThumbnail;
    }

    public function setYoutubeThumbnail(?Attachment $youtubeThumbnail): self
    {
        $this->youtubeThumbnail = $youtubeThumbnail;

        return $this;
    }

    public function isVideoPremium(): bool
    {
        return $this->isPremium() || $this->isScheduled();
    }

    public function getCountdown(): ?int
    {
        if ($this->isScheduled()) {
            return $this->getPublishedAt()->getTimestamp() - time();
        }

        return null;
    }

    public function isRestrictedToUser(): bool
    {
        return $this->isRestrictedToUser;
    }

    public function setIsRestrictedToUser(bool $isRestrictedToUser): self
    {
        $this->isRestrictedToUser = $isRestrictedToUser;
        return $this;
    }

    public function getYoutubeTitle(): ?string
    {
        return $this->youtubeTitle;
    }

    public function setYoutubeTitle(?string $youtubeTitle): self
    {
        $this->youtubeTitle = $youtubeTitle;
        return $this;
    }

}