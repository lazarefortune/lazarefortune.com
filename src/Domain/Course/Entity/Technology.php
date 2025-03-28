<?php

namespace App\Domain\Course\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
class Technology
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['tech_summary'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['tech_summary'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['tech_summary'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tech_summary'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'icons', fileNameProperty: 'image')]
    #[Groups(['tech_summary'])]
    private ?File $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'technology', targetEntity: TechnologyUsage::class, orphanRemoval: true)]
    private Collection $usages;

    private bool $secondary = false;

    private ?string $version = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Technology>
     */
    #[ORM\JoinTable(name: 'technology_requirement')]
    #[ORM\ManyToMany(targetEntity: Technology::class, inversedBy: 'requiredBy')]
    #[Groups(['tech_relations'])]
    private Collection $requirements;

    /**
     * @var Collection<int, Technology>
     */
    #[ORM\ManyToMany(targetEntity: Technology::class, mappedBy: 'requirements')]
    private Collection $requiredBy;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $type = null;

    public function __construct() {
        $this->usages = new ArrayCollection();
        $this->requirements = new ArrayCollection();
        $this->requiredBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        if (null === $this->slug && $this->name) {
            $this->slug = (new Slugify())->slugify($this->name);
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, TechnologyUsage>
     */
    public function getUsages(): Collection
    {
        return $this->usages;
    }

    public function addUsage(TechnologyUsage $usage): self
    {
        if (!$this->usages->contains($usage)) {
            $this->usages[] = $usage;
            $usage->setTechnology($this);
        }

        return $this;
    }

    public function removeUsage(TechnologyUsage $usage): self
    {
        if ($this->usages->contains($usage)) {
            $this->usages->removeElement($usage);
        }

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function setSecondary(bool $secondary): self
    {
        $this->secondary = $secondary;

        return $this;
    }

    public function isSecondary(): bool
    {
        return $this->secondary;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): Technology
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): Technology
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Technology>
     */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(self $requirement): self
    {
        if (!$this->requirements->contains($requirement)) {
            $this->requirements[] = $requirement;
        }

        return $this;
    }

    public function removeRequirement(self $requirement): self
    {
        if ($this->requirements->contains($requirement)) {
            $this->requirements->removeElement($requirement);
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Technology>
     */
    public function getRequiredBy() : Collection
    {
        return $this->requiredBy;
    }


}