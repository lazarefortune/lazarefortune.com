<?php

namespace App\Domain\Feedback\Enum;

enum FeedbackType: string
{
    case IDEA = 'idea';
    case BUG = 'bug';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IDEA => 'Proposition d’idée',
            self::BUG => 'Signalement de bug',
            self::OTHER => 'Autre retour',
        };
    }
}
