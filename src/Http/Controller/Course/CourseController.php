<?php

namespace App\Http\Controller\Course;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\CourseService;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\History\Service\HistoryService;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Controller\AbstractController;
use App\Http\Requirements;
use App\Http\Security\CourseVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

#[Route('/videos', name: 'course_')]
class CourseController extends AbstractController
{

    public function __construct(
        private readonly CourseService         $courseService,
        private readonly HistoryService        $historyService,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->courseService->getCourseList();
        $page = $request->query->getInt('page', 1);

        $courses = $paginator->paginate( $query->setMaxResults(12)->getQuery() );

        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            $watchlist = $this->historyService->getLastWatchedContent($user);
        }

        return $this->render('pages/public/courses/index.html.twig', [
            'courses' => $courses,
            'page' => $page,
            'watchlist' => $watchlist ?? []
        ]);
    }

    #[Route('/{slug<[a-z0-9A-Z\-]+>}', name: 'show', methods: ['GET'])]
    public function show(CourseRepository $courseRepository, string $slug): Response
    {
        /** @var Course $course */
        $course = $courseRepository->findOneBy(['slug' => $slug]);

        if (null === $course) {
            throw $this->createNotFoundException();
        }

        if ($course->getSlug() !== $slug) {
            return $this->redirectToRoute('course_show', [
                'id' => $course->getId(),
                'slug' => $course->getSlug()
            ], 301);
        }

        if ( !$course->isOnline() ) {
            throw $this->createNotFoundException();
        }
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('pages/public/courses/show.html.twig', [
            'course' => $course,
            'userIsPremium' => ( $user && $user->isPremium() )
        ]);
    }

    #[Route(path: '/{id}/sources', name: 'download_source', requirements: ['id' => Requirements::ID])]
    public function downloadSource(Course $course, StorageInterface $storage): Response
    {
        $this->denyAccessUnlessGranted(CourseVoter::DOWNLOAD_SOURCE);

        if (null === $course->getSource()) {
            throw new NotFoundHttpException();
        }

        $path = $storage->resolvePath($course, 'sourceFile', null, true);

        return new Response(
            file_get_contents($this->getParameter('kernel.project_dir') . '/public/uploads/sources/' . $path),
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $course->getSource() . '"'
            ]
        );
        # return $this->redirectToRoute('app_download_source', ['source' => $path]);
    }
}