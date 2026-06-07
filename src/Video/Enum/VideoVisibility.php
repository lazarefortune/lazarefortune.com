<?php

declare(strict_types=1);

namespace App\Video\Enum;

enum VideoVisibility: string
{
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
    case PUBLIC = 'public';
}
