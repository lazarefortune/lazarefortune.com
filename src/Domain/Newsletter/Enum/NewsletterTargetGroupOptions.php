<?php
namespace App\Domain\Newsletter\Enum;

enum NewsletterTargetGroupOptions: string
{
    case ALL = 'all';
    case USERS = 'users';
    case SUBSCRIBERS = 'subscribers';

    public function label(): string
    {
        return match($this) {
            self::ALL => 'Tous le monde',
            self::USERS => 'Utilisateurs',
            self::SUBSCRIBERS => 'Abonnés uniquement',
        };
    }
}