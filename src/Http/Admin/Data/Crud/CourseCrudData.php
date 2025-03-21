<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Technology;
use App\Http\Admin\Data\CrudDataInterface;
use App\Http\Form\AutomaticForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Handler\UploadHandler;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Slug;
use App\Validator\Exists;

class CourseCrudData implements CrudDataInterface
{

    #[Assert\NotBlank]
    public ?string $title = null;

    #[Slug]
    #[Assert\NotBlank]
    public ?string $slug = null;

    public ?User $author;

    #[Assert\NotBlank]
    public ?\DateTimeInterface $publishedAt;

    public bool $online = false;

    public bool $source = false;

    public bool $premium = false;

    public ?string $youtube = null;

    public ?string $videoPath = null;

    #[Exists(class: Course::class)]
    public ?int $deprecatedBy = null;

    #[Assert\NotBlank]
    public ?string $content = null;

    public ?int $duration = 0;

    public ?Attachment $image = null;

    public ?Attachment $youtubeThumbnail = null;

    #[Assert\File(mimeTypes: ['application/zip'])]
    public ?UploadedFile $sourceFile = null;

    /**
     * @var Technology[]
     */
    public array $mainTechnologies = [];

    /**
     * @var Technology[]
     */
    public array $secondaryTechnologies = [];

    private EntityManagerInterface $em;

    public function __construct(
        private readonly Course         $entity,
        private readonly ?UploadHandler $uploaderHandler = null
    ) {
        $this->title = $entity->getTitle();
        $this->slug = $entity->getSlug();
        $this->author = $entity->getAuthor();
        $this->publishedAt = $entity->getPublishedAt();
        $this->videoPath = $entity->getVideoPath();
        $this->image = $entity->getImage();
        $this->online = $entity->isOnline();
        $this->premium = $entity->isPremium();
        $this->content = $entity->getContent() ?: '';
        $this->youtube = $entity->getYoutubeId();
        $this->duration = $entity->getDuration();
        $this->source = !empty($entity->getSource());
        $this->mainTechnologies = $entity->getMainTechnologies();
        $this->secondaryTechnologies = $entity->getSecondaryTechnologies();
        $this->youtubeThumbnail = $entity->getYoutubeThumbnail();
        $deprecatedBy = $entity->getDeprecatedBy();
        $this->deprecatedBy = $deprecatedBy?->getId();
    }

    public function hydrate(): void
    {
        $this->entity->setTitle($this->title);
        $this->entity->setSlug($this->slug);
        $this->entity->setAuthor($this->author);
        $deprecatedBy = $this->deprecatedBy;
        $this->entity->setDeprecatedBy($deprecatedBy ? $this->em->find(Course::class, $deprecatedBy) : null);
        $this->entity->setVideoPath($this->videoPath);
        $this->entity->setImage($this->image);
        $this->entity->setYoutubeThumbnail($this->youtubeThumbnail);
        $this->entity->setOnline($this->online);
        $this->entity->setSourceFile($this->sourceFile);
        $this->entity->setYoutubeId($this->youtube);
        $this->entity->setPremium($this->premium);
        $this->entity->setContent($this->content);
        $this->entity->setPublishedAt($this->publishedAt);
        $this->entity->setUpdatedAt(new \DateTime());
        foreach ($this->mainTechnologies as $technology) {
            $technology->setSecondary(false);
        }
        foreach ($this->secondaryTechnologies as $technology) {
            $technology->setSecondary(true);
        }
        $removed = $this->entity->syncTechnologies(array_merge($this->mainTechnologies, $this->secondaryTechnologies));
        if ($this->entity->getId()) {
            foreach ($removed as $usage) {
                $this->em->remove($usage);
            }
        }
        // On ne veut plus de source pour le tutoriel
        if ($this->uploaderHandler && !$this->source && $this->entity->getSource()) {
            $this->uploaderHandler->remove($this->entity, 'sourceFile');
        }
    }

    public function getEntity(): Course
    {
        return $this->entity;
    }

    public function getFormClass(): string
    {
        return AutomaticForm::class;
    }

    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }
}