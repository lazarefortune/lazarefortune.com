<?php

namespace App\Domain\Newsletter\Enum;

enum NewsletterStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Programmé',
            self::SENT => 'Envoyé',
        };
    }
}