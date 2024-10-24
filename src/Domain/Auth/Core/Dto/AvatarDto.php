<?php

namespace App\Domain\Auth\Core\Dto;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AvatarDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Image(mimeTypes: ['image/jpeg', 'image/png'])]
        public UploadedFile $file,
        public User $user
    ) {}
}