<?php

namespace App\Http\Controller\Course;

use App\Domain\Course\CourseService;
use App\Domain\Course\Entity\Course;
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

        return $this->render('pages/public/courses/index.html.twig', [
            'courses' => $courses,
            'page' => $page,
        ]);
    }

    #[Route('/{slug<[a-z0-9A-Z\-]+>}', name: 'show', methods: ['GET'])]
    public function show(Course $course, string $slug): Response
    {
        if ($course->getSlug() !== $slug) {
            return $this->redirectToRoute('course_show', [
                'id' => $course->getId(),
                'slug' => $course->getSlug()
            ], 301);
        }

        $response = new Response();
        if ( false === $course->isOnline() ) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $this->render('pages/public/courses/show.html.twig', [
            'course' => $course,
        ]);
    }
}