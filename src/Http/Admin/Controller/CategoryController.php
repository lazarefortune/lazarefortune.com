<?php

namespace App\Http\Admin\Controller;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Form\NewCategoryForm;
use App\Domain\Category\Repository\CategoryRepository;
use App\Http\Admin\Data\Crud\CategoryCrudData;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/category' , name: 'category_prestation_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class CategoryController extends CrudController
{
    protected string $templatePath = 'category';
    protected string $menuItem = 'category';
    protected string $entity = Category::class;
    protected string $routePrefix = 'admin_category_prestation';
    protected array $events = [];

    #[Route( '/', name: 'index', methods: ['GET'] )]
    public function index( CategoryRepository $categoryRepository ) : Response
    {
        $this->paginator->allowSort( 'row.id', 'row.name' );
        $query = $categoryRepository
            ->createQueryBuilder( 'row' )
            ->orderBy( 'row.id', 'DESC' );

        return $this->crudIndex( $query );
    }

    #[Route( '/new', name: 'new', methods: ['POST', 'GET'] )]
    public function new(): Response
    {
        $category = new Category();
        $data = new CategoryCrudData( $category );

        return $this->crudNew( $data );
    }

    #[Route( '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'] )]
    public function edit( Category $category ) : Response
    {
        $data = new CategoryCrudData( $category );

        return $this->crudEdit( $data );
    }

    #[Route( '/{id<\d+>}/ajax-delete', name: 'delete', methods: ['DELETE'] )]
    public function delete( Category $category ) : Response
    {
        return $this->crudAjaxDelete( $category );
    }

}