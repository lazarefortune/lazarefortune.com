<?php

declare(strict_types=1);

namespace App\Content\Enum;

enum ContentVisibility: string
{
    case PUBLIC = 'public';
    case MEMBERS_ONLY = 'members_only';
}
