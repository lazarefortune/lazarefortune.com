<?php

declare(strict_types=1);

namespace App\Content\Contract;

use App\Auth\Entity\User;

interface PubliclyVisible
{
    public function isPubliclyVisible(?User $viewer): bool;
}
