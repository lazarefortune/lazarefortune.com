<?php

declare(strict_types=1);

namespace App\Studio\Twig;

use App\Studio\Service\StudioBreadcrumbBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class StudioExtension extends AbstractExtension
{
    public function __construct(
        private readonly StudioBreadcrumbBuilder $breadcrumbBuilder,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('studio_breadcrumb', $this->buildBreadcrumb(...)),
        ];
    }

    /**
     * @return list<array{label: string, route: ?string, icon: ?string}>
     */
    public function buildBreadcrumb(?string $routeName = null): array
    {
        $routeName ??= $this->requestStack->getCurrentRequest()?->attributes->get('_route');

        if (!is_string($routeName)) {
            return [];
        }

        return $this->breadcrumbBuilder->build($routeName);
    }
}
