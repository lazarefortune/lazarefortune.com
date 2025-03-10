<?php

namespace App\Http\Controller;

use App\Domain\Badge\BadgeService;
use App\Domain\Badge\Entity\Badge;
use App\Http\Requirements;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BadgeController extends AbstractController
{
    #[Route(path: '/badge/unlock/{action:badge}', name: 'badge_unlock', requirements: ['action' => Requirements::SLUG])]
    #[IsGranted('ROLE_USER')]
    public function unlock(
        Badge $badge,
        BadgeService $service,
    ): RedirectResponse {
        if (!$badge->isUnlockable()) {
            throw new NotFoundHttpException();
        }
        $unlocks = $service->unlock($this->getUserOrThrow(), $badge->getAction());
        if (null === $unlocks || empty($unlocks)) {
            $this->addFlash('error', 'Vous avez déjà ce badge');
        }

        $this->addFlash('success', 'Vous avez déjà ce badge');

        return $this->redirectToRoute('user_badges');
    }
}