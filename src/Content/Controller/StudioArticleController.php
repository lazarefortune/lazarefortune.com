<?php

declare(strict_types=1);

namespace App\Content\Controller;

use App\Content\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/studio')]
final class StudioArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {
    }

    #[Route('/articles', name: 'studio_article_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('studio/article/index.html.twig', [
            'articles' => $this->articleRepository->findLatestForStudio(),
        ]);
    }
}
