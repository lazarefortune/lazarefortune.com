<?php

namespace App\Domain\Badge\Service;

namespace App\Domain\Badge\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Badge\Entity\Badge;
use App\Domain\Badge\Entity\BadgeUnlock;
use Doctrine\ORM\EntityManagerInterface;

class BadgeManager
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Vérifie si l'utilisateur peut débloquer un badge pour l'action donnée.
     */
    public function checkAndUnlockBadges(User $user, string $action, int $actionCount): void
    {
        // Récupérer tous les badges correspondants à l'action
        $badgeRepo = $this->em->getRepository(Badge::class);
        $badges = $badgeRepo->findBy(['action' => $action]);

        foreach ($badges as $badge) {
            if ($actionCount >= $badge->getActionCount()) {
                // Vérifier si pas déjà débloqué
                $alreadyUnlocked = $this->em->getRepository(BadgeUnlock::class)
                    ->findOneBy(['owner' => $user, 'badge' => $badge]);
                if (!$alreadyUnlocked) {
                    $badgeUnlock = new BadgeUnlock($user, $badge);
                    $this->em->persist($badgeUnlock);
                }
            }
        }
        $this->em->flush();
    }
}
