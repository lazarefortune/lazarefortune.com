<?php

namespace App\Http\Api\Controller;

use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Search\Service\SearchService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/search', name: 'search_')]
class SearchController extends AbstractController
{

    public function __construct(
        private readonly SearchService $searchService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/{query}', name: 'index', methods: ['GET'])]
    public function index( $query ): JsonResponse
    {
        $results = $this->searchService->search($query);

        // if is results check if is formation or course
        if (count($results) > 0) {
            foreach ($results as $key => $result) {
                if ($result instanceof Formation) {
                    $results[$key] = [
                        'type' => 'formation',
                        'id' => $result->getId(),
                        'title' => $result->getTitle(),
                        'url' => $this->urlGenerator->generate('app_formation_show', ['slug' => $result->getSlug()]),
                        'image' => $result->getImage(),
                        'duration' => $result->getDuration(),
                        'createdAt' => $result->getCreatedAt(),
                        'updatedAt' => $result->getUpdatedAt(),
                    ];
                } else if ($result instanceof Course) {
                    $results[$key] = [
                        'type' => 'course',
                        'id' => $result->getId(),
                        'title' => $result->getTitle(),
                        'url' => $this->urlGenerator->generate('app_course_show', ['slug' => $result->getSlug()]),
                        'image' => $result->getImage(),
                        'duration' => $result->getDuration(),
                        'createdAt' => $result->getCreatedAt(),
                        'updatedAt' => $result->getUpdatedAt(),
                    ];
                }
            }
        }

        return $this->json($results);
    }
}