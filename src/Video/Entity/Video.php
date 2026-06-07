<?php

declare(strict_types=1);

namespace App\Video\Entity;

use App\Content\Entity\Content;
use App\Video\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'videos')]
class Video extends Content
{
    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(nullable: true)]
    private ?int $durationSeconds = null;

    /** @var Collection<int, VideoSource> */
    #[ORM\OneToMany(mappedBy: 'video', targetEntity: VideoSource::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $sources;

    public function __construct(\App\Auth\Entity\User $author)
    {
        parent::__construct($author);
        $this->sources = new ArrayCollection();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }

    /** @return Collection<int, VideoSource> */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function addSource(VideoSource $source): static
    {
        if (!$this->sources->contains($source)) {
            $this->sources->add($source);
            $source->setVideo($this);
        }

        return $this;
    }

    public function removeSource(VideoSource $source): static
    {
        $this->sources->removeElement($source);

        return $this;
    }

    public function getPrimarySource(): ?VideoSource
    {
        foreach ($this->sources as $source) {
            if ($source->isPrimary()) {
                return $source;
            }
        }

        return null;
    }
}
