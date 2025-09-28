<?php

namespace App\Http\Controller\Course;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\CourseService;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Course\Repository\TechnologyRepository;
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
    public function index(CourseRepository $repo, Request $request, PaginatorInterface $paginator, TechnologyRepository $technologyRepository): Response
    {
        $isUserPremium = $this->getUser()?->isPremium();
        $query = $repo->queryAll($isUserPremium ?? false);
        $page = $request->query->getInt('page', 1);

        // Filtre par technologie
        $technologySlug = $request->query->get('technology');
        $technology = null;
        if ($technologySlug) {
            $technology = $technologyRepository->findOneBy(['slug' => $technologySlug]);
            if (null !== $technology) {
                $query = $query->setParameter('technology', $technology)->leftJoin('c.technologyUsages', 'tu')->andWhere('tu.technology = :technology');
            }
        }

        // Tri par défaut (plus récent)
        $query->orderBy('c.publishedAt', 'DESC');

        // Créer la query finale avec le tri déjà appliqué
        $finalQuery = $query->setMaxResults(12)->getQuery();

        // Paginer sans tri automatique en désactivant le tri
        $courses = $paginator->paginate($finalQuery, $page, 12, [
            'defaultSortFieldName' => 'c.publishedAt',
            'defaultSortDirection' => 'DESC'
        ]);

        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            $watchlist = $this->historyService->getLastWatchedContent($user);
        }

        return $this->render('pages/public/courses/index.html.twig', [
            'courses' => $courses,
            'page' => $page,
            'technology_selected' => $technology,
            'watchlist' => $watchlist ?? [],
            'technologies' => $technologyRepository->findByType(),
        ], new Response('', $courses->count() > 0 ? 200 : 404));
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
        try {
            $this->denyAccessUnlessGranted(CourseVoter::DOWNLOAD_SOURCE);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Vous devez être premium pour télécharger les sources des vidéos.');
            return $this->redirectToRoute('app_premium');
        }

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
