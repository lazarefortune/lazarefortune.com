<?php

namespace App\Domain\Application\Entity;

use App\Domain\Application\Repository\ContentRepository;
use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Auth\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Domain\Course\Entity\TechnologyUsage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[ORM\DiscriminatorMap(['course' => Course::class, 'formation' => Formation::class])]
abstract class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $online = false;

    #[ORM\ManyToOne(targetEntity: Attachment::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'attachment_id', referencedColumnName: 'id')]
    private ?Attachment $image = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $premium = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $author = null;

    #[ORM\OneToMany( mappedBy: 'content', targetEntity: TechnologyUsage::class, cascade: ['persist'] )]
    private Collection $technologyUsages;

    public function __construct()
    {
        $this->technologyUsages = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

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

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt ?: new \DateTime();
    }

    /**
     * @return $this
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @return $this
     */
    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getImage(): ?Attachment
    {
        return $this->image;
    }

    public function setImage(?Attachment $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): self
    {
        $this->premium = $premium;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, TechnologyUsage>
     */
    public function getTechnologyUsages(): Collection
    {
        return $this->technologyUsages;
    }

    public function addTechnologyUsage(TechnologyUsage $technologyUsage): self
    {
        if (!$this->technologyUsages->contains($technologyUsage)) {
            $this->technologyUsages[] = $technologyUsage;
            $technologyUsage->setContent($this);
        }

        return $this;
    }

    /**
     * @return Technology[]
     */
    public function getTechnologies(): array
    {
        return $this->getTechnologyUsages()->map(fn (TechnologyUsage $usage) => $usage->getTechnology())->toArray();
    }

    public function getMainTechnologies(): array
    {
        return $this->getFilteredTechnology();
    }

    public function getSecondaryTechnologies(): array
    {
        return $this->getFilteredTechnology(true);
    }

    /**
     * Synchronise les technologies à partir d'un tableau de technology avec des valeurs de version
     * et de secondary hydraté.
     *
     * @return array<TechnologyUsage> Relation TechnologyUsage détachés de l'entité (qu'il faudra supprimer)
     */
    public function syncTechnologies(array $technologies): array
    {
        $currentTechnologies = $this->getTechnologies();

        // On commence par synchronisé les usages
        /** @var TechnologyUsage $usage */
        foreach ($this->getTechnologyUsages() as $usage) {
            $usage->setVersion($usage->getTechnology()->getVersion());
            $usage->setSecondary($usage->getTechnology()->isSecondary());
        }

        // On ajoute les nouveaux usage
        /** @var Technology[] $newUsage */
        $newUsage = array_diff($technologies, $currentTechnologies);
        foreach ($newUsage as $technology) {
            $usage = (new TechnologyUsage())
                ->setSecondary($technology->isSecondary())
                ->setTechnology($technology)
                ->setVersion($technology->getVersion());
            $this->addTechnologyUsage($usage);
        }

        // On supprime les technologies qui n'existe pas dans notre nouvelle liste
        $removed = [];
        $newUsage = [];
        foreach ($this->technologyUsages as $usage) {
            if (!in_array($usage->getTechnology(), $technologies)) {
                $removed[] = $usage;
            } else {
                $newUsage[] = $usage;
            }
        }
        $this->technologyUsages = new ArrayCollection($newUsage);

        return $removed;
    }

    private function getFilteredTechnology(bool $secondary = false): array
    {
        $technologies = [];
        foreach ($this->getTechnologyUsages() as $usage) {
            if ($usage->getSecondary() === $secondary) {
                $technologies[] = $usage->getTechnology()->setVersion($usage->getVersion());
            }
        }

        return $technologies;
    }


    public function isCourse(): bool
    {
        return $this instanceof Course;
    }

    public function isFormation(): bool
    {
        return $this instanceof Formation;
    }

    public function isScheduled(): bool
    {
        return new \DateTimeImmutable() < $this->getCreatedAt();
    }
}