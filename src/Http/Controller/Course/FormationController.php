<?php

namespace App\Http\Controller\Course;

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
        private readonly ProgressRepository $progressRepository
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $formations = $this->formationService->getFormations();
        return $this->render('pages/public/formations/index.html.twig', [
            'formations' => $formations
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

        $user = $this->getUser();
        $progress = null;
        if ($user) {
            $progress = $this->progressRepository->findOneByContent($user, $formation);
        }

        return $this->render('pages/public/formations/show.html.twig', [
            'formation' => $formation,
            'progress' => $progress,
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