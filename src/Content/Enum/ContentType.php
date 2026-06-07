<?php

declare(strict_types=1);

namespace App\Content\Enum;

use App\Content\Entity\Article;
use App\Video\Entity\Video;

/**
 * Doctrine discriminator values for the Content inheritance tree.
 * Not a freely editable field: the concrete entity class is the source of truth.
 */
enum ContentType: string
{
    case VIDEO = 'video';
    case ARTICLE = 'article';

    public static function fromClass(string $class): self
    {
        return match ($class) {
            Video::class => self::VIDEO,
            Article::class => self::ARTICLE,
            default => throw new \InvalidArgumentException(sprintf('Unsupported content class "%s".', $class)),
        };
    }
}
