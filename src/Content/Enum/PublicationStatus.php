<?php

declare(strict_types=1);

namespace App\Content\Enum;

enum PublicationStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
