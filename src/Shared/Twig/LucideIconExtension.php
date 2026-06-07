<?php

declare(strict_types=1);

namespace App\Shared\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LucideIconExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lucide_icon', $this->renderIcon(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderIcon(string $name, int $size = 20, ?string $class = null, ?string $label = null): string
    {
        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return '';
        }

        $path = sprintf('%s/node_modules/lucide-static/icons/%s.svg', $this->projectDir, $name);
        if (!is_readable($path)) {
            return '';
        }

        $svg = file_get_contents($path);
        if ($svg === false) {
            return '';
        }

        $svg = preg_replace('/<!--.*?-->/s', '', $svg) ?? $svg;

        $classes = trim(sprintf('lucide lucide-%s inline-block shrink-0 %s', $name, $class ?? ''));
        $aria = $label !== null && $label !== ''
            ? sprintf(' role="img" aria-label="%s"', htmlspecialchars($label, ENT_QUOTES))
            : ' aria-hidden="true"';

        $replacement = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="%s"%s>',
            $size,
            $size,
            htmlspecialchars($classes, ENT_QUOTES),
            $aria,
        );

        $svg = preg_replace('/<svg[^>]*>/', $replacement, $svg, 1);
        if ($svg === null) {
            return '';
        }

        return $svg;
    }
}
