<?php

namespace App\Http\Twig;

use App\Domain\Auth\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class TwigUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly UploaderHelperInterface $uploaderHelper
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('avatar', $this->avatarPath(...)),
        ];
    }

    public function avatarPath(User $user): string
    {
        if ($user->getAvatar() === null) {
            return '/images/avatars/default.jpg';
        }

        return $this->uploaderHelper->asset($user, 'avatarFile');
    }

}