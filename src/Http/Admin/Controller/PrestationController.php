<?php

namespace App\Http\Admin\Controller;

use App\Domain\Prestation\Entity\Prestation;
use App\Domain\Prestation\Form\PrestationForm;
use App\Domain\Prestation\PrestationService;
use App\Domain\Prestation\Repository\PrestationRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/prestations', name: 'prestation_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class PrestationController extends AbstractController
{
    #[Route( '/', name: 'index' )]
    public function index( PrestationService $prestationService ) : Response
    {
        $prestations = $prestationService->getAll();

        return $this->render( 'admin/prestations/index.html.twig', [
            'prestations' => $prestations,
        ] );
    }

    #[Route( '/new', name: 'new' )]
    public function new( Request $request, PrestationService $prestationService ) : Response
    {
        $prestation = new Prestation();
        $form = $this->createForm( PrestationForm::class, $prestation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {

            $prestationService->save( $prestation );

            $this->addFlash( 'success', 'Prestation créée avec succès' );
            return $this->redirectToRoute( 'app_admin_prestation_index', [], Response::HTTP_SEE_OTHER );
        }

        return $this->render( 'admin/prestations/new.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}/edit', name: 'edit' )]
    public function edit( Request $request, Prestation $prestation, PrestationService $prestationService ) : Response
    {
        $form = $this->createForm( PrestationForm::class, $prestation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {

            $prestationService->save( $prestation );

            $this->addFlash( 'success', 'Prestation modifiée avec succès' );
            return $this->redirectToRoute( 'app_admin_prestation_index', [], Response::HTTP_SEE_OTHER );
        }

        return $this->render( 'admin/prestations/edit.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'delete', methods: ['POST'] )]
    public function delete( Request $request, Prestation $prestation, PrestationRepository $prestationRepository ) : Response
    {
        if ( $this->isCsrfTokenValid( 'delete' . $prestation->getId(), $request->request->get( '_token' ) ) ) {
            $prestationRepository->remove( $prestation, true );

            $this->addFlash( 'success', 'Prestation supprimée avec succès' );
        }

        return $this->redirectToRoute( 'app_admin_prestation_index', [], Response::HTTP_SEE_OTHER );
    }
}
