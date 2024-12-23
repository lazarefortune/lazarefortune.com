<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Badge\Entity\Badge;
use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Unique;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property Badge $entity
 */
#[Unique(field: 'name')]
class BadgeCrudData extends AutomaticCrudData
{
    #[Assert\NotBlank]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public ?string $description = null;

    #[Assert\NotBlank]
    public string $action = '';

    #[Assert\NotBlank]
    public string $theme = 'grey';

    #[Assert\NotBlank]
    public int $actionCount = 0;

    public bool $unlockable = false;

    public ?UploadedFile $imageFile = null;

    public function hydrate(): void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt(new \DateTimeImmutable());
    }
}