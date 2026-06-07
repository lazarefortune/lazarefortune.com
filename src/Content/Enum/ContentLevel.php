<?php

declare(strict_types=1);

namespace App\Content\Enum;

enum ContentLevel: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
}
