<?php

namespace App\Http\Controller\Course;

use App\Domain\Course\CourseService;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tutoriels', name: 'course_')]
class CourseController extends AbstractController
{

    public function __construct(private readonly CourseService $courseService)
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->courseService->getCourseList();
        $page = $request->query->getInt('page', 1);

        $courses = $paginator->paginate( $query->setMaxResults(10)->getQuery() );

        return $this->render('courses/index.html.twig', [
            'courses' => $courses,
            'page' => $page,
        ]);
    }
}