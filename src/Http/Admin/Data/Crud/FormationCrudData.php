<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Auth\Entity\User;
use App\Domain\Course\Entity\Chapter;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Http\Admin\Data\CrudDataInterface;
use App\Http\Form\AutomaticForm;
use App\Validator\Exists;
use App\Validator\Slug;
use App\Validator\Unique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(field: 'slug')]
class FormationCrudData implements CrudDataInterface
{
    private ?EntityManagerInterface $em = null;

    #[Assert\NotBlank]
    public ?string $title;

    #[Slug]
    #[Assert\NotBlank]
    public ?string $slug;

    public ?\DateTimeInterface $publishedAt;

    public ?User $author;

    public ?string $youtubePlaylist;

    public bool $online;

    public ?Attachment $image;

    /**
     * @var Technology[]
     */
    public array $mainTechnologies = [];

    /**
     * @var Technology[]
     */
    public array $secondaryTechnologies = [];

    #[Assert\NotBlank]
    public ?string $content;

    public ?string $short;

    /**
     * @var Chapter[]
     */
    public array $chapters;

    #[Exists(class: Formation::class)]
    public ?int $deprecatedBy = null;

    public function __construct(private readonly Formation $formation)
    {
        $this->title = $formation->getTitle();
        $this->slug = $formation->getSlug();
        $this->author = $formation->getAuthor();
        $this->publishedAt = $formation->getPublishedAt();
        $this->youtubePlaylist = $formation->getYoutubePlaylist();
        $this->online = $formation->isOnline();
        $this->image = $formation->getImage();
        $this->mainTechnologies = $formation->getMainTechnologies();
        $this->secondaryTechnologies = $formation->getSecondaryTechnologies();
        $this->short = $formation->getShort();
        $this->content = $formation->getContent();
        $this->chapters = $formation->getChapters();
        $deprecatedBy = $formation->getDeprecatedBy();
        $this->deprecatedBy = $deprecatedBy?->getId();
    }

    public function getEntity(): Formation
    {
        return $this->formation;
    }

    public function getFormClass(): string
    {
        return AutomaticForm::class;
    }

    public function hydrate(): void
    {
        $this->formation->setTitle($this->title);
        $this->formation->setSlug($this->slug);
        $this->formation->setPublishedAt($this->publishedAt);
        $this->formation->setUpdatedAt(new \DateTime());
        $this->formation->setAuthor($this->author);
        $this->formation->setYoutubePlaylist($this->youtubePlaylist);
        $this->formation->setOnline($this->online);
        $this->formation->setImage($this->image);
        $this->formation->setShort($this->short);
        $this->formation->setContent($this->content);
        if ($this->em) {
            $deprecatedBy = $this->deprecatedBy;
            $this->formation->setDeprecatedBy($deprecatedBy ? $this->em->find(Formation::class, $deprecatedBy) : null);
        }
        foreach ($this->mainTechnologies as $technology) {
            $technology->setSecondary(false);
        }
        foreach ($this->secondaryTechnologies as $technology) {
            $technology->setSecondary(true);
        }
        $removed = $this->formation->syncTechnologies(array_merge($this->mainTechnologies, $this->secondaryTechnologies));
        if ($this->em) {
            foreach ($removed as $usage) {
                $this->em->remove($usage);
            }
        }
        /** @var Course $course */
        foreach ($this->formation->getCourses() as $course) {
            $course->setFormation(null);
        }
        foreach ($this->chapters as $chapter) {
            /** @var Course $course */
            foreach ($chapter->getModules() as $course) {
                $course->setFormation($this->formation);
            }
        }
        $this->formation->setChapters($this->chapters);
    }

    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->getEntity()->getId();
    }
}