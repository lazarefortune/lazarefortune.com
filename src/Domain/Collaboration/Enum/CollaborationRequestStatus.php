<?php

namespace App\Domain\Collaboration\Enum;

enum CollaborationRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Refusée',
        };
    }
}
