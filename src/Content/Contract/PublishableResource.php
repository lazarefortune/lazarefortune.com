<?php

declare(strict_types=1);

namespace App\Content\Contract;

use App\Content\Enum\ContentVisibility;
use App\Content\Enum\PublicationStatus;

interface PublishableResource extends PubliclyVisible
{
    public function getStatus(): PublicationStatus;

    public function getVisibility(): ContentVisibility;
}
