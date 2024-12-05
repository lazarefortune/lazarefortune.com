<?php

namespace App\Http\Admin\Controller;

use App\Domain\Quiz\Entity\Quiz;
use App\Http\Admin\Data\Crud\QuizCrudData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/quiz', name: 'quiz_')]
#[IsGranted('ROLE_ADMIN')]
class QuizController extends CrudController
{
    protected string $templatePath = 'quiz';
    protected string $entity = Quiz::class;
    protected string $menuItem = 'quiz';
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'admin_quiz';
    protected array $events = [
        'update' => null,
        'delete' => null,
        'create' => null,
    ];

    #[Route( path: '/', name: 'index' )]
    public function index() : Response
    {
        return $this->crudIndex();
    }

    #[Route( path: '/nouveau', name: 'new' )]
    #[IsGranted('ROLE_AUTHOR')]
    public function new() : Response
    {
        $entity = new Quiz();
        $data = new QuizCrudData( $entity );

        return $this->crudNew( $data );
    }

    #[Route( path: '/{id}', name: 'edit' )]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit( Quiz $quiz ) : Response
    {
        $data = new QuizCrudData( $quiz );

        return $this->crudEdit( $data );
    }

    #[Route( path: '/{id}/ajax-delete', name: 'delete', methods: [ 'DELETE' ] )]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete( Quiz $quiz ) : Response
    {
        return $this->crudAjaxDelete( $quiz );
    }
}
