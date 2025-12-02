<?php

namespace App\Http\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('body_theme', $this->getUserTheme(...)),
        ];
    }

    public function getUserTheme(): string
    {
        // TODO: A terme on pourra ajouter un champ dans la table user pour stocker le thème

        $request = $this->requestStack->getCurrentRequest();

        return ( $request?->cookies->has( 'theme' ) ) ? $request?->cookies->get( 'theme' ) : '';
    }
}