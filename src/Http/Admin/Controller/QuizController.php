<?php

namespace App\Http\Admin\Controller;

use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/quiz', name: 'quiz_')]
class QuizController extends CrudController
{
    protected string $templatePath = 'quiz';
    #protected string $entity = Quiz::class;
    protected string $menuItem = 'quiz';
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'admin_quiz';
    protected array $events = [
        'update' => null,
        'delete' => null,
        'create' => null,
    ];


}