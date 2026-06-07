<?php

declare(strict_types=1);

namespace App\Video\Enum;

enum VideoProvider: string
{
    case YOUTUBE = 'youtube';
    case VIMEO = 'vimeo';
    case SELF_HOSTED = 'self_hosted';
    case EXTERNAL_URL = 'external_url';
}
