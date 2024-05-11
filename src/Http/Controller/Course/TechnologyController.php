<?php

namespace App\Http\Controller\Course;

use App\Domain\Course\Entity\Technology;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Course\Repository\FormationRepository;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TechnologyController extends AbstractController
{
    #[Route('/technologie/{slug}', name: 'technology_show', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function index(
        Technology $technology,
        FormationRepository $formationRepository,
        CourseRepository $courseRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $page = $request->query->getInt('page', 1);

        $formations = [];
        if ($page <= 1) {
            $formations = $formationRepository->findByTechnology($technology);
        }

        $courses = $paginator->paginate($courseRepository->queryForTechnology($technology));
        $nextTechnologies = collect($technology->getRequiredBy())->groupBy(fn (Technology $t) => $t->getType() ?? '');

        $isEmpty = count($formations) === 0 && $courses->getTotalItemCount() === 0;

        return $this->render('courses/technology.html.twig', [
            'technology' => $technology,
            'nextTechnologies' => $nextTechnologies,
            'showTabs' => count($formations) >= 3,
            'isEmpty' => $isEmpty,
            'formations' => $formations,
            'courses' => $courses,
        ]);
    }
}