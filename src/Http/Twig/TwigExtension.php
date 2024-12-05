<?php

namespace App\Http\Twig;

use App\Helper\CentToEuroTransformer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct( private readonly CentToEuroTransformer $centToEuroTransformer )
    {
    }


    public function getFunctions() : array
    {
        return [
            new TwigFunction( 'icon', $this->showIcon( ... ), ['is_safe' => ['html']] ),
            new TwigFunction( 'menu_active_aria', $this->menuActiveAria( ... ), ['is_safe' => ['html'], 'needs_context' => true] ),
            new TwigFunction( 'menu_active', $this->menuActive( ... ), ['is_safe' => ['html'], 'needs_context' => true] ),
            new TwigFunction( 'pluralize', [$this, 'pluralize'] ),
            new TwigFunction('is_instance_of', [$this, 'isInstanceOf']),
        ];
    }

    public function getFilters() : array
    {
        return [
            new TwigFilter( 'duration_format', [$this, 'durationFormat'] ),
            new TwigFilter( 'money', [$this, 'priceFormat'] ),
            new TwigFilter( 'price_convert', [$this, 'priceConvert'] ),
            new TwigFilter( 'is_older_than_hours', [$this, 'isOlderThanHours'] ),
            new TwigFilter( 'json_decode', [$this, 'jsonDecode'] ),
            new TwigFilter( 'date_age', [$this, 'formatDateAge'] ),
            new TwigFilter( 'human_date', [$this, 'formatHumanDate'] ),
            new TwigFilter( 'hour_lisible', [$this, 'formatHourLisible'] ),
            new TwigFilter( 'date_diff', [$this, 'dateDiff'] ),
            new TwigFilter( 'price_format', [$this, 'priceFormat'] ),
            new TwigFilter( 'truncate', [$this, 'truncate'] ),
        ];
    }

    public function isInstanceOf($object, $class): bool
    {
        return $object instanceof $class;
    }

    public function truncate( string $text, int $length = 30, string $ending = '...' ) : string
    {
        if ( strlen( $text ) <= $length ) {
            return $text;
        }

        $text = substr( $text, 0, $length );

        return $text . $ending;

    }

    public function formatPrice( $value ) : string
    {
        return $this->centToEuroTransformer->transform( $value ) . ' €';
    }

    public function formatHourLisible( \DateTime $date ) : string
    {
        return $date->format( 'H\hi' );
    }

    public function formatHumanDate( \DateTime $date ) : string
    {
        return $date->format( 'j F Y' );
    }

    public function pluralize( int $count, string $singular, ?string $plural = null ) : string
    {
        if ( $count > 1 ) {
            return $plural ?? $singular . 's';
        }

        return $singular;
    }

    public function formatDateAge( \DateTime $date ) : string
    {
        $now = new \DateTime();
        $interval = $now->diff( $date );

        if ( $interval->days == 0 ) {
            if ( $interval->h == 0 ) {
                return 'il y a ' . $interval->i . ' ' . $this->pluralize( $interval->i, 'minute' );
            } else {
                return 'il y a ' . $interval->h . ' ' . $this->pluralize( $interval->h, 'heure' );
            }
        } elseif ( $interval->days == 1 ) {
            return 'hier';
        } elseif ( $interval->days < 30 ) {
            return 'il y a ' . $interval->days . ' jours';
        } elseif ( $interval->days < 365 ) {
            $months = round( $interval->days / 30 );
            return 'il y a ' . $months . ' mois';
        } else {
            $years = round( $interval->days / 365 );
            return 'il y a ' . $years . ' ' . $this->pluralize( $years, 'an' );
        }
    }

    public function dateDiff( $date ) : string
    {
        if ( !$date instanceof \DateTime && !$date instanceof \DateTimeImmutable ) {
            return 'Invalid date';
        }

        $now = new \DateTime();
        $interval = $now->diff( $date );

        return $this->formatDateDiff( $interval, $date < $now );
    }

    private function formatDateDiff( \DateInterval $interval, bool $isPast ) : string
    {
        $format = '';

        if ( $interval->y > 0 ) {
            $format .= $interval->y . ' ' . $this->pluralize( $interval->y, 'an' );
        } elseif ( $interval->m > 0 ) {
            $format .= $interval->m . ' ' . $this->pluralize( $interval->m, 'mois', 'mois' );
        } elseif ( $interval->d > 0 ) {
            $format .= $interval->d . ' ' . $this->pluralize( $interval->d, 'jour' );
        } elseif ( $interval->h > 0 ) {
            $format .= $interval->h . ' ' . $this->pluralize( $interval->h, 'heure' );
        } elseif ( $interval->i > 0 ) {
            $format .= $interval->i . ' ' . $this->pluralize( $interval->i, 'minute' );
        } else {
            $format .= $interval->s . ' ' . $this->pluralize( $interval->s, 'seconde' );
        }

        if ( $isPast ) {
            return 'il y a ' . $format;
        } else {
            return 'dans ' . $format;
        }
    }


    /**
     * @param string $json
     * @return array<string, mixed>
     */
    public function jsonDecode( string $json ) : array
    {
        return json_decode( $json, true );
    }

    public function isOlderThanHours( \DateTimeImmutable $dateTime, int $hours ) : bool
    {
        $now = new \DateTimeImmutable();
        $interval = $now->diff( $dateTime );

        $totalHours = $interval->h + ( $interval->days * 24 );

        return $totalHours >= $hours;
    }

    public function priceConvert( $value ) : string
    {
        return $this->centToEuroTransformer->transform( $value );
    }

    public function priceFormat( $value ) : string
    {
        return $this->centToEuroTransformer->transform( $value ) . ' €';
    }

    public function durationFormat( ?\DateTime $dateTime ) : string
    {
        if ( !$dateTime ) {
            return '';
        }

        $hours = $dateTime->format( 'H' );
        $minutes = $dateTime->format( 'i' );

        if ( $hours > 0 ) {
            return $hours . 'h' . $minutes;
        }

        return $minutes . ' min';
    }

    public function showIcon( string $iconName, ?int $iconSize = null, ?string $additionalClass = null ) : string
    {
        return $this->svgIcon( $iconName, $iconSize, $additionalClass );
    }

    /**
     * Show an svg icon from the sprite.
     * @param string $name
     * @param int|null $size
     * @param string|null $additionalClass
     * @return string
     */
    public function svgIcon( string $name, ?int $size = null, ?string $additionalClass = null ) : string
    {
        $attrs = '';

        $size = $size ?? '17';

        if ( $size ) {
            $attrs = " width=\"{$size}px\" height=\"{$size}px\"";
        }

        if ( $additionalClass ) {
            $attrs .= " class=\"{$additionalClass}\"";
        }

        return <<<HTML
            <svg class="icon"
                 {$attrs}
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.75"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <use href="/icons/sprite.svg?#{$name}"></use>
            </svg>
        HTML;
    }

    /**
     * Add an active class for active menu items.
     * @param array<string, mixed> $context
     * @param string $route
     * @return string
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
     * Add an aria-current="page" attribute for active menu items.
     * @param array<string, mixed> $context
     * @param string $route
     * @return string
     */
    public function menuActiveAria( array $context, string $route ) : string
    {
        $active = '';
        $request = $context['app']->getRequest();
        $currentRoute = $request->get( '_route' );

        if ( str_starts_with( $currentRoute, $route ) ) {
            $active = 'aria-current="page"';
        }

        return $active;
    }

}