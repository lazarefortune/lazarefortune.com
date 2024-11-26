<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Entity\Content;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/content', name: 'content_')]
final class ContentController extends BaseController
{
    /**
     * Endpoint pour récupérer le titre d'un cours ou d'une formation depuis son Id.
     */
    #[Route(path: '/{id<\d+>}/title', name: 'title')]
    public function title(Content $content): JsonResponse
    {
        return new JsonResponse([
            'id' => $content->getId(),
            'title' => $content->getTitle(),
        ]);
    }

    /**
     * Endpoint pour rechercher des cours par titre.
     */
    #[Route(path: '/search', name: 'search')]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (empty($query)) {
            return new JsonResponse([]);
        }

        $courses = $em->getRepository(Course::class)
            ->createQueryBuilder('c')
            ->where('c.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = array_map(function (Course $course) {
            return [
                'id' => $course->getId(),
                'title' => $course->getTitle(),
            ];
        }, $courses);

        return new JsonResponse($results);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit')]
    public function edit(Content $content): RedirectResponse
    {
        if ($content instanceof Formation) {
            $path = 'admin_formation_edit';
        } elseif ($content instanceof Course) {
            $path = 'admin_course_edit';
        } else {
            throw new NotFoundHttpException();
        }

        return $this->redirectToRoute($path, ['id' => $content->getId()]);
    }
}