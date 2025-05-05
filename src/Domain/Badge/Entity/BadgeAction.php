<?php

namespace App\Domain\Badge\Entity;

class BadgeAction
{
    public const COMMENTS = 'comments';
    public const DAYS = 'days';
    public const PUBLISHED_VIDEOS = 'published_videos';

    public static function getLabels(): array
    {
        return [
            self::COMMENTS => 'Commentaires',
            self::DAYS => 'Ancienneté',
            self::PUBLISHED_VIDEOS => 'Vidéos publiées',
        ];
    }

    public static function getLabel(string $action): string
    {
        return self::getLabels()[$action] ?? ucfirst($action);
    }

    public static function getChoices(): array
    {
        // Inversé pour le ChoiceType : 'label' => 'value'
        return array_flip(self::getLabels());
    }
}
