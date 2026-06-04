<?php

namespace App\Http\Studio\Controller;


use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Formation;
use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Http\Admin\Controller\CrudController;
use App\Http\Admin\Form\Formation\FormationEditForm;
use App\Http\Admin\Form\Formation\FormationNewForm;
use App\Http\Security\ContentVoter;
use App\Infrastructure\Search\Meilisearch\MeilisearchException;
use App\Infrastructure\Search\SearchInterface;
use App\Infrastructure\Search\SearchResultItemInterface;
use App\Infrastructure\Youtube\YoutubeService;
use App\Infrastructure\Youtube\YoutubeUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Event\Subscriber\Paginate\Callback\CallbackPagination;
use Knp\Component\Pager\PaginatorInterface as KnpPaginatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_AUTHOR')]
#[Route(path: '/playlists', name: 'formation_')]
final class FormationController extends CrudController
{
    protected string $templateDirectory = 'pages/studio';
    protected string $templatePath = 'formation';
    protected string $menuItem = 'formation';
    protected string $entity = Formation::class;
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'studio_formation';
    protected string $searchField = 'title';
    protected array $events = [
        'update' => ContentUpdatedEvent::class,
        'delete' => ContentDeletedEvent::class,
        'create' => ContentCreatedEvent::class,
    ];

    private const SESSION_FORMATION_ID = 'session_formation_id';

    public function __construct(
        EntityManagerInterface $em,
        \App\Helper\Paginator\PaginatorInterface $paginator,
        EventDispatcherInterface $dispatcher,
        RequestStack $requestStack,
        private readonly SearchInterface $search,
        private readonly KnpPaginatorInterface $knpPaginator,
    ) {
        parent::__construct($em, $paginator, $dispatcher, $requestStack);
    }

    #[Route(path: '/', name: 'index')]
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        if ('' !== $q) {
            return $this->indexWithSearch($request, $q);
        }

        return $this->indexWithoutSearch();
    }

    private function indexWithoutSearch(): Response
    {
        $this->paginator->allowSort('row.id');
        $query = $this->getRepository()
            ->createQueryBuilder('row')
            ->leftJoin('row.technologyUsages', 'tu')
            ->leftJoin('tu.technology', 't')
            ->addSelect('t', 'tu')
            ->where('row.author = :author')
            ->orderby('row.createdAt', 'DESC')
            ->setParameter('author', $this->getUser())
            ->setMaxResults(10);

        return $this->crudIndex($query);
    }

    private function indexWithSearch(Request $request, string $q): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $filters = ['author_id' => $this->getUser()->getId()];

        try {
            $result = $this->search->search($q, ['formation'], $limit, $page, $filters, ['title']);
            $ids = array_map(
                static fn (SearchResultItemInterface $item) => $item->getId(),
                $result->getItems()
            );
            $formations = $this->findFormationsByIds($ids);
            $pagination = new CallbackPagination(
                static fn () => $result->getTotal(),
                static fn () => $formations,
            );
            $rows = $this->knpPaginator->paginate($pagination, $page, $limit);
        } catch (MeilisearchException) {
            return $this->indexWithSqlSearch($request);
        }

        return $this->render("{$this->templateDirectory}/{$this->templatePath}/index.html.twig", [
            'rows' => $rows,
            'searchable' => true,
            'menu' => $this->menuItem,
            'prefix' => $this->routePrefix,
        ]);
    }

    private function indexWithSqlSearch(Request $request): Response
    {
        $this->paginator->allowSort('row.id');
        $query = $this->getRepository()
            ->createQueryBuilder('row')
            ->leftJoin('row.technologyUsages', 'tu')
            ->leftJoin('tu.technology', 't')
            ->addSelect('t', 'tu')
            ->where('row.author = :author')
            ->orderby('row.createdAt', 'DESC')
            ->setParameter('author', $this->getUser())
            ->setMaxResults(10);

        return $this->crudIndex($query);
    }

    /**
     * @param int[] $ids
     *
     * @return Formation[]
     */
    private function findFormationsByIds(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        /** @var Formation[] $formations */
        $formations = $this->getRepository()
            ->createQueryBuilder('row')
            ->leftJoin('row.technologyUsages', 'tu')
            ->leftJoin('tu.technology', 't')
            ->addSelect('t', 'tu')
            ->where('row.id IN (:ids)')
            ->andWhere('row.author = :author')
            ->setParameter('ids', $ids)
            ->setParameter('author', $this->getUser())
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($formations as $formation) {
            $indexed[$formation->getId()] = $formation;
        }

        return array_values(array_filter(array_map(
            static fn (int $id) => $indexed[$id] ?? null,
            $ids
        )));
    }

    protected function applySearch(string $search, QueryBuilder $query): QueryBuilder
    {
        return $query
            ->andWhere('LOWER(row.title) LIKE :search')
            ->setParameter('search', '%'.strtolower($search).'%');
    }

    #[Route(path: '/nouveau', name: 'new', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function new(Request $request, SessionInterface $session, EventDispatcherInterface $dispatcher): Response
    {
        $formation = (new Formation())->setAuthor($this->getUser());

        $form = $this->createForm( FormationNewForm::class, $formation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $formation->setUpdatedAt(new \DateTime());
            $this->em->persist($formation);
            $this->em->flush();

            $dispatcher->dispatch(new ContentCreatedEvent($formation), ContentCreatedEvent::NAME);

            $session->set(self::SESSION_FORMATION_ID, $formation->getId());
            return $this->redirectToRoute('studio_formation_upload');
        }

        return $this->render('pages/studio/formation/new.html.twig', [
            'form' => $form->createView(),
            'entity' => $formation,
        ]);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(
        Formation $formation,
        Request $request,
        EventDispatcherInterface $dispatcher,
        YoutubeService $youtubeService,
        YoutubeUploaderService $youtubeUploaderService,
        MessageBusInterface $messageBus
    ): Response {
        $this->denyAccessUnlessGranted(ContentVoter::EDIT , $formation );

        $oldFormation = clone $formation;
        $form = $this->createForm(FormationEditForm::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newFormation = $form->getData();
            $newFormation->setUpdatedAt(new \DateTime());
            $this->em->flush();
            $this->addFlash('success', 'La playlist a bien été modifiée');

            $dispatcher->dispatch(new ContentUpdatedEvent($newFormation, $oldFormation), ContentUpdatedEvent::NAME);

            if ($request->request->get('synchronize')) {
                $this->handleFormationUpload(
                    $formation->getId(),
                    true,
                    $youtubeService,
                    $youtubeUploaderService,
                    $messageBus
                );
                return $this->redirectToRoute('studio_formation_edit', ['id' => $formation->getId()]);
            }
        }

        return $this->render('pages/studio/formation/edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $formation,
        ]);
    }


    #[Route(path: '/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(Formation $formation): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::DELETE , $formation );
        return $this->crudAjaxDelete($formation);
    }

    #[Route('/upload', name: 'upload', methods: ['GET'])]
    public function upload(
        SessionInterface $session,
        MessageBusInterface $messageBus,
        YoutubeService $youtubeService,
        YoutubeUploaderService $youtubeUploaderService
    ): Response {
        $formationId = $session->get(self::SESSION_FORMATION_ID);
        $session->remove(self::SESSION_FORMATION_ID);

        if (!$formationId) {
            $this->addFlash('danger', "Impossible d'uploader la formation, ID manquant.");
            return $this->redirectToRoute('studio_formation_index');
        }

        // upload synchronisé lors de la création
        $this->handleFormationUpload(
            $formationId,
            false,
            $youtubeService,
            $youtubeUploaderService,
            $messageBus
        );

        return $this->redirectToRoute('studio_formation_edit', ['id' => $formationId]);
    }


    #[Route('/{id}/trigger-upload', name: 'trigger_upload', methods: ['POST'])]
    public function triggerUpload(Formation $formation, SessionInterface $session): Response
    {
        $session->set(self::SESSION_FORMATION_ID, $formation->getId());
        return $this->redirectToRoute('formation_upload');
    }

    private function handleFormationUpload(
        int $formationId,
        bool $async,
        YoutubeService $youtubeService,
        YoutubeUploaderService $youtubeUploaderService,
        MessageBusInterface $messageBus
    ): void {
        $youtubeSetting = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);
        if (!$youtubeSetting || !$youtubeSetting->getAccessToken()) {
            $this->addFlash('danger', "Aucun compte YouTube configuré.");
            return;
        }

        $youtubeService->authenticateGoogleClient($youtubeSetting);
        $tokenArray = json_decode($youtubeSetting->getAccessToken(), true);

        if ($async) {
            $this->dispatchMethod(
                $messageBus,
                YoutubeUploaderService::class,
                'uploadFormation',
                [$formationId, $tokenArray]
            );
            $this->addFlash('success', "La playlist est en cours d'envoi sur Youtube");
        } else {
            $youtubeUploaderService->uploadFormation($formationId, $tokenArray);
            $this->addFlash('success', "La playlist a été ajoutée sur Youtube");
        }
    }

}