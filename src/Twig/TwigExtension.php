<?php

namespace App\Twig;

use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('date_format', [$this, 'dateFormat']),
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction( 'icon', $this->showIcon( ... ), ['is_safe' => ['html']] ),
            new TwigFunction( 'menu_active', $this->menuActive( ... ), ['is_safe' => ['html'], 'needs_context' => true] ),
            new TwigFunction('calculate_duration', [$this, 'calculateDuration']),
        ];
    }

    /**
     * Affiche une icône.
     */
    public function showIcon( string $icon, ?string $size = null, ?string $attrs = null ) : string
    {
        $attributes = '';
        if ( $size ) {
            $attributes = 'la-' . $size;
        }

        if ( $attrs ) {
            $attributes .= ' ' . $attrs;
        }

        return <<<HTML
            <i class="las la-{$icon}  {$attributes}"></i>
        HTML;
    }

    /**
     * Ajout une class active pour les éléments actifs du menu.
     */
    public function menuActive( array $context, string $route ) : string
    {
        $active = '';
        $request = $context['app']->getRequest();
        $currentRoute = $request->get( '_route' );

        if ( str_starts_with( $currentRoute, $route ) ) {
            $active = 'active';
        }

        return $active;
    }

    /**
     * Calcule la durée entre deux dates.
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @return string
     */
    public function calculateDuration( DateTimeInterface $start, DateTimeInterface $end): string
    {
        $duration = $start->diff($end);
        $hours = $duration->h;
        $minutes = $duration->i;

        if ($hours > 0) {
            $hours = $hours . ' h ';
        } else {
            $hours = '';
        }

        if ($minutes > 0) {
            $minutes = $minutes . ' min';
        } else {
            $minutes = '';
        }

        return $hours . $minutes;
    }

    /**
     * Formate un nombre en prix.
     * @param $number
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     * @return string
     */
    public function formatPrice( $number , int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ' ') : string
    {
        $price = number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
        return $price . ' €';
    }

    /**
     * Formate une date.
     * @param DateTimeInterface $date
     * @param string $format
     * @return string
     */
    public function dateFormat( DateTimeInterface $date, string $format = 'd/m/Y'): string
    {
        return $date->format($format);
    }
}