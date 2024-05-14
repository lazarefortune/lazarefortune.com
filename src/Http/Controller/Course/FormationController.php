<?php

namespace App\Http\Controller\Course;

use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Service\FormationService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formations', name: 'formation_')]
class FormationController extends AbstractController
{
    public function __construct(
        private readonly FormationService $formationService
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $formations = $this->formationService->getFormations();
        return $this->render('formations/index.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formations/show.html.twig', [
            'formation' => $formation
        ]);
    }
}