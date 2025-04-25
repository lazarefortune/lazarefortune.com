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
use App\Infrastructure\Youtube\YoutubeService;
use App\Infrastructure\Youtube\YoutubeUploaderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
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
    protected array $events = [
        'update' => ContentUpdatedEvent::class,
        'delete' => ContentDeletedEvent::class,
        'create' => ContentCreatedEvent::class,
    ];

    private const SESSION_FORMATION_ID = 'session_formation_id';

    #[Route(path: '/', name: 'index')]
    public function index(): Response
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
            ->setMaxResults(10)
        ;

        return $this->crudIndex($query);
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