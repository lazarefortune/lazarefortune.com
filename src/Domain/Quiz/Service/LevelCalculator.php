<?php

namespace App\Domain\Quiz\Service;

class LevelCalculator
{
    public function getLevelForXp(int $xp): int
    {
        // Par exemple:
        // Niveau 1 = 0-999 XP
        // Niveau 2 = 1000-1999 XP
        // Niveau 3 = 2000-2999 XP, etc.
        return intdiv($xp, 1000) + 1;
    }
}
