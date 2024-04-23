<?php

namespace App\Http\Admin\Controller;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Form\NewCategoryForm;
use App\Domain\Category\Repository\CategoryRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/category' )]
#[IsGranted( 'ROLE_ADMIN' )]
class CategoryController extends AbstractController
{
    #[Route( '/', name: 'category_prestation_index', methods: ['GET'] )]
    public function index( CategoryRepository $categoryPrestationRepository ) : Response
    {
        return $this->render( 'admin/category/index.html.twig', [
            'category_prestations' => $categoryPrestationRepository->findAll(),
        ] );
    }

    #[Route( '/new', name: 'category_prestation_new', methods: ['GET', 'POST'] )]
    public function new( Request $request, CategoryRepository $categoryPrestationRepository ) : Response
    {
        $categoryPrestation = new Category();
        $form = $this->createForm( NewCategoryForm::class, $categoryPrestation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $categoryPrestationRepository->save( $categoryPrestation, true );

            $this->addFlash( 'success', 'Catégorie de prestation créée avec succès' );
            return $this->redirectToRoute( 'app_admin_category_prestation_index', [], Response::HTTP_SEE_OTHER );
        }

        return $this->render( 'admin/category/new.html.twig', [
            'category_service' => $categoryPrestation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}/edit', name: 'category_service_edit', methods: ['GET', 'POST'] )]
    public function edit( Request $request, Category $categoryPrestation, CategoryRepository $categoryPrestationRepository ) : Response
    {
        $form = $this->createForm( NewCategoryForm::class, $categoryPrestation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $categoryPrestationRepository->save( $categoryPrestation, true );

            $this->addFlash( 'success', 'Catégorie de prestation modifiée avec succès' );
            return $this->redirectToRoute( 'app_admin_category_prestation_index', [], Response::HTTP_SEE_OTHER );
        }

        return $this->render( 'admin/category/edit.html.twig', [
            'category_service' => $categoryPrestation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'category_prestation_delete', methods: ['POST', 'DELETE'] )]
    public function delete( Request $request, Category $categoryPrestation, CategoryRepository $categoryPrestationRepository ) : Response
    {
        if ( $this->isCsrfTokenValid( 'delete' . $categoryPrestation->getId(), $request->request->get( '_token' ) ) ) {
            // TODO: check if category has prestations
            
            $this->addFlash( 'success', 'Catégorie de prestation supprimée avec succès' );
            $categoryPrestationRepository->remove( $categoryPrestation, true );
        }

        return $this->redirectToRoute( 'app_admin_category_prestation_index', [], Response::HTTP_SEE_OTHER );
    }
}
