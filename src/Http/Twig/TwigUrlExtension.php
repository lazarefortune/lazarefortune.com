<?php

namespace App\Http\Twig;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class TwigUrlExtension extends AbstractExtension
{

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UploaderHelperInterface $uploaderHelper,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', $this->pathFor(...)),
            new TwigFunction('url', $this->urlFor(...)),
        ];
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

    /**
     * @param string|object $path
     */
    public function pathFor($path, array $params = []): string
    {
        if (is_string($path)) {
            return $this->urlGenerator->generate($path, $params);
        }

        return $this->serializer->serialize($path, 'path', ['url' => false]);
    }

    /**
     * @param string|object $path
     */
    public function urlFor($path, array $params = []): string
    {
        if (is_string($path)) {
            return $this->urlGenerator->generate(
                $path,
                $params,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->serializer->serialize($path, 'path', ['url' => true]);
    }

}