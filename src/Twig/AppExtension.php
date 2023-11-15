<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'priceFilter']),
        ];
    }

    public function priceFilter($number, $decimals = 2, $decPoint = ',', $thousandsSep = ' ') : string
    {
        return number_format($number, $decimals, $decPoint, $thousandsSep) . ' €';
    }
}