<?php

namespace App\Http\Admin\Controller;


use App\Domain\Course\Entity\Formation;
use App\Http\Admin\Data\Crud\FormationCrudData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/formation', name: 'formation_')]
final class FormationController extends CrudController
{
    protected string $templatePath = 'formation';
    protected string $menuItem = 'formation';
    protected string $entity = Formation::class;
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'app_admin_formation';
    protected array $events = [];

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
    public function new(): Response
    {
        $entity = (new Formation())->setAuthor($this->getUser());
        $data = new FormationCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Formation $formation): Response
    {
        $data = (new FormationCrudData($formation))->setEntityManager($this->em);

        return $this->crudEdit($data);
    }

    #[Route(path: '/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Formation $formation): Response
    {
        return $this->crudDelete($formation);
    }
}