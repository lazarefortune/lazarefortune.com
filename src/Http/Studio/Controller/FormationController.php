<?php

namespace App\Http\Studio\Controller;


use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Formation;
use App\Http\Admin\Controller\CrudController;
use App\Http\Admin\Data\Crud\FormationCrudData;
use App\Http\Security\ContentVoter;
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
            ->orderby('row.createdAt', 'DESC')
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
    public function edit(Formation $formation): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::EDIT , $formation );
        $data = (new FormationCrudData($formation))->setEntityManager($this->em);

        return $this->crudEdit($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(Formation $formation): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::DELETE , $formation );
        return $this->crudAjaxDelete($formation);
    }
}