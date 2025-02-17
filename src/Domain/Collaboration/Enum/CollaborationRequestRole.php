<?php

namespace App\Domain\Collaboration\Enum;

enum CollaborationRequestRole: string
{
    case AUTHOR = 'ROLE_AUTHOR';
    case MODERATOR = 'ROLE_MODERATOR';

    public function label(): string
    {
        return match ($this) {
            self::AUTHOR => 'Auteur (création de contenu)',
            self::MODERATOR => 'Modérateur du site',
        };
    }

    public static function choices(): array
    {
        return [
            'Auteur (création de contenu)' => self::AUTHOR,
            'Modérateur du site' => self::MODERATOR,
        ];
    }
}
