<?php

namespace App\Http\Studio\Controller;


use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Formation;
use App\Http\Admin\Controller\CrudController;
use App\Http\Admin\Data\Crud\FormationCrudData;
use App\Http\Admin\Form\Formation\FormationEditForm;
use App\Http\Security\ContentVoter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function new(): Response
    {
        $entity = (new Formation())->setAuthor($this->getUser());
        $data = new FormationCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(Formation $formation, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::EDIT , $formation );

        $oldFormation = clone $formation;
        $form = $this->createForm( FormationEditForm::class, $formation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $newFormation = $form->getData();
            $this->em->flush();
            $this->addFlash('success', 'La playlist a bien été modifié');

            $dispatcher->dispatch(new ContentUpdatedEvent($oldFormation, $newFormation));
        }

        # $data = (new FormationCrudData($formation))->setEntityManager($this->em);

        #return $this->crudEdit($data);

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
}