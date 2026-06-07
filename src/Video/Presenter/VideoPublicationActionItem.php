<?php

declare(strict_types=1);

namespace App\Video\Presenter;

final readonly class VideoPublicationActionItem
{
    public function __construct(
        public string $label,
        public ?string $form = null,
        public ?string $href = null,
        public ?string $icon = null,
        public ?string $testid = null,
    ) {
    }
}
