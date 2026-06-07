<?php

declare(strict_types=1);

namespace App\Comment\Enum;

enum CommentStatus: string
{
    case PUBLISHED = 'published';
    case HIDDEN = 'hidden';
    case DELETED = 'deleted';
}
