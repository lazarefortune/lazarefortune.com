<?php

namespace App\Http\Twig;



use App\Helper\TimeHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigTimeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('duration', $this->duration(...) ),
            new TwigFilter('countdown', $this->countdown(...), ['is_safe' => ['html']]),
            new TwigFilter('duration_short', $this->shortDuration(...), ['is_safe' => ['html']]),
        ];
    }

    public function duration(int $duration): string
    {
        return TimeHelper::duration($duration);
    }

    /**
     * Génère une durée au format court hh:mm:ss.
     */
    public function shortDuration(int $duration): string
    {
        $minutes = floor($duration / 60);
        $seconds = $duration - $minutes * 60;
        $times = [$minutes, $seconds];
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes - ($hours * 60);
            $times = [$hours, $minutes, $seconds];
        }

        return implode(':', array_map(
            fn (int|float $duration) => str_pad(strval($duration), 2, '0', STR_PAD_LEFT),
            $times
        ));
    }

    public function countdown(\DateTimeInterface $date): string
    {
        return "<time-countdown time=\"{$date->getTimestamp()}\"></time-countdown>";
    }
}