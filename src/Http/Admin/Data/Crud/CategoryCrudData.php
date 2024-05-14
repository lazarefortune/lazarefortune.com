<?php

namespace App\Http\Admin\Data\Crud;

use App\Http\Admin\Data\AutomaticCrudData;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryCrudData extends AutomaticCrudData
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = '';

    public ?string $description = '';

    public ?bool $isActive = null;

}