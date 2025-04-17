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

class CourseNewCrudData implements CrudDataInterface
{

    #[Assert\NotBlank]
    public ?string $title = null;

    public ?string $youtubeTitle = null;

    #[Slug]
    #[Assert\NotBlank]
    public ?string $slug = null;

    public ?User $author;

    #[Assert\NotBlank]
    public ?\DateTimeInterface $publishedAt;

    public bool $premium = false;

    #[Exists(class: Course::class)]
    public ?int $deprecatedBy = null;

    #[Assert\NotBlank]
    public ?string $content = null;

    public ?Attachment $image = null;

    public ?Attachment $youtubeThumbnail = null;

    /**
     * @var Technology[]
     */
    public array $mainTechnologies = [];

    /**
     * @var Technology[]
     */
    public array $secondaryTechnologies = [];

    public bool $isRestrictedToUser = false;

    private EntityManagerInterface $em;

    public function __construct(
        private readonly Course         $entity,
        private readonly ?UploadHandler $uploaderHandler = null
    ) {
        $this->title = $entity->getTitle();
        $this->youtubeTitle = $entity->getYoutubeTitle();
        $this->slug = $entity->getSlug();
        $this->author = $entity->getAuthor();
        $this->publishedAt = $entity->getPublishedAt();
        $this->image = $entity->getImage();
        $this->premium = $entity->isPremium();
        $this->isRestrictedToUser = $entity->isRestrictedToUser();
        $this->content = $entity->getContent() ?: '';
        $this->mainTechnologies = $entity->getMainTechnologies();
        $this->secondaryTechnologies = $entity->getSecondaryTechnologies();
        $deprecatedBy = $entity->getDeprecatedBy();
        $this->youtubeThumbnail = $entity->getYoutubeThumbnail();
        $this->deprecatedBy = $deprecatedBy?->getId();
    }

    public function hydrate(): void
    {
        $this->entity->setTitle($this->title);
        $this->entity->setYoutubeTitle($this->youtubeTitle);
        $this->entity->setSlug($this->slug);
        $this->entity->setAuthor($this->author);
        $this->entity->setIsRestrictedToUser($this->isRestrictedToUser);
        $deprecatedBy = $this->deprecatedBy;
        $this->entity->setDeprecatedBy($deprecatedBy ? $this->em->find(Course::class, $deprecatedBy) : null);
        $this->entity->setImage($this->image);
        $this->entity->setPremium($this->premium);
        $this->entity->setContent($this->content);
        $this->entity->setPublishedAt($this->publishedAt);
        $this->entity->setUpdatedAt(new \DateTime());
        $this->entity->setYoutubeThumbnail($this->youtubeThumbnail);
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