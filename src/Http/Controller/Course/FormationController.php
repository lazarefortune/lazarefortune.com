<?php

namespace App\Http\Controller\Course;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Service\FormationService;
use App\Domain\History\Repository\ProgressRepository;
use App\Domain\History\Service\HistoryService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/playlists', name: 'formation_')]
class FormationController extends AbstractController
{
    public function __construct(
        private readonly FormationService $formationService,
        private readonly ProgressRepository $progressRepository,
        private readonly HistoryService $historyService,
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $formations = $this->formationService->getFormations();

        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            $watchlist = $this->historyService->getLastWatchedContent($user);
        }
        return $this->render('pages/public/formations/index.html.twig', [
            'formations' => $formations,
            'watchlist' => $watchlist ?? []
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
//        if ($formation->isForceRedirect() && $formation->getDeprecatedBy()) {
//            $newFormation = $formation->getDeprecatedBy();
//
//            return $this->redirectToRoute('formation_show', [
//                'slug' => $newFormation->getSlug(),
//            ], 301);
//        }

        // Check if formation is online
        if (!$formation->isOnline()) {
            throw $this->createNotFoundException();
        }

        $progress = null;

        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            $progress = $this->progressRepository->findOneByContent($user, $formation);
            $watchlist = $this->historyService->getLastWatchedContent($user);
        }

        return $this->render('pages/public/formations/show.html.twig', [
            'formation' => $formation,
            'progress' => $progress,
            'watchlist' => $watchlist ?? []
        ]);
    }

    /**
     * Redirect to the next chapter.
     */
    #[Route(path: '/formations/{slug}/continue', name: 'resume')]
    public function resume(
        Formation $formation,
        HistoryService $historyService,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer
    ): RedirectResponse {
        $user = $this->getUser();
        $ids = $formation->getModulesIds();
        $nextContentId = $ids[0];
        if (null !== $user) {
            $nextContentId = $historyService->getNextContentIdToWatch($user, $formation) ?: $ids[0];
        }
        $content = $em->find(Course::class, $nextContentId);
        /** @var array $path */
        $path = $normalizer->normalize($content, 'path');

        return $this->redirectToRoute($path['path'], $path['params']);
    }
}