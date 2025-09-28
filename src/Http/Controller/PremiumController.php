<?php

namespace App\Http\Controller;

use App\Domain\Premium\Repository\PlanRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PremiumController extends AbstractController
{
    #[Route( '/abonnement/premium' , name: 'premium' )]
    public function premium(PlanRepository $planRepository): Response
    {
        throw $this->createAccessDeniedException('Les abonnements sont temporairement suspendus');

        $plans = $planRepository->findall();

        return $this->render('pages/public/premium/premium.html.twig', [
            'plans' => $plans,
            'menu' => 'premium',
        ]);
    }
}
