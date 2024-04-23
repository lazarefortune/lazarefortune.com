<?php

namespace App\Http\Controller;

use App\Domain\Prestation\Repository\PrestationRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/services', name: 'prestation_')]
class PrestationController extends AbstractController
{
    public function __construct(readonly PrestationRepository $prestationRepository)
    {
    }

    #[Route('/', name: 'list')]
    public function prestation() : Response
    {
        $prestations = $this->prestationRepository->findAll();
        return $this->render('pages/prestation.html.twig', [
            'prestations' => $prestations
        ]);
    }
}