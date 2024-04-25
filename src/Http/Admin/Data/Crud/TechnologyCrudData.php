<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Course\Entity\Technology;
use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Slug;
use App\Validator\Unique;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property Technology $entity
 */
#[Unique(field: 'slug'), Unique(field: 'name')]
class TechnologyCrudData extends AutomaticCrudData
{
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Slug]
    #[Assert\NotBlank]
    public ?string $slug = null;

    public ?Collection $requirements = null;

    public ?string $content = null;

    public ?UploadedFile $imageFile = null;

    public function hydrate(): void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt(new \DateTime());
    }
}