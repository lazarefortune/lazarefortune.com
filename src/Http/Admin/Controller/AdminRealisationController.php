<?php

namespace App\Http\Admin\Controller;

use App\Domain\Realisation\Entity\ImageRealisation;
use App\Domain\Realisation\Entity\Realisation;
use App\Domain\Realisation\Form\RealisationForm;
use App\Domain\Realisation\Repository\RealisationRepository;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/realisations', name: 'realisation_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class AdminRealisationController extends AbstractController
{
    #[Route( '/', name: 'index', methods: ['GET'] )]
    public function index( RealisationRepository $realisationRepository ) : Response
    {

//        $this->denyAccessUnlessGranted( 'can_manage_roles' );

        $realisations = $realisationRepository->findBy(
            [],
            ['dateRealisation' => 'DESC']
        );

        return $this->render( 'admin/realisation/index.html.twig', [
            'realisations' => $realisations,
        ] );
    }

    #[Route( '/new', name: 'new', methods: ['GET', 'POST'] )]
    public function new( Request $request, RealisationRepository $realisationRepository, EntityManagerInterface $entityManager ) : Response
    {
        $realisation = new Realisation();
        $form = $this->createForm( RealisationForm::class, $realisation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $this->uploadImagesForRealisation( $form, $realisation, $entityManager );

            $dateRealisation = $form->get( 'dateRealisation' )->getData();
            if ( empty( $dateRealisation ) ) {
                $realisation->setDateRealisation( new \DateTime() );
            }

            $realisation->setCreatedAt( new \DateTimeImmutable() );

            $realisationRepository->save( $realisation, true );

            $this->addFlash( 'success', 'La réalisation a bien été ajoutée' );
            return $this->redirectToRoute( 'app_admin_realisation_index', [], Response::HTTP_SEE_OTHER );
        }

        return $this->render( 'admin/realisation/new.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'show', methods: ['GET'] )]
    public function show( Realisation $realisation ) : Response
    {
        return $this->render( 'admin/realisation/show.html.twig', [
            'realisation' => $realisation,
        ] );
    }

    #[Route( '/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'POST'] )]
    public function edit( Request $request, Realisation $realisation, RealisationRepository $realisationRepository, EntityManagerInterface $entityManager ) : Response
    {
        $form = $this->createForm( RealisationForm::class, $realisation );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $this->uploadImagesForRealisation( $form, $realisation, $entityManager );

            $realisationRepository->save( $realisation, true );

            $this->addFlash( 'success', 'La réalisation a bien été modifiée' );
            return $this->redirect( $request->headers->get( 'referer' ) );
        }

        return $this->render( 'admin/realisation/edit.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'delete', methods: ['POST', 'DELETE'] )]
    public function delete( Request $request, Realisation $realisation, RealisationRepository $realisationRepository ) : Response
    {
        if ( $this->isCsrfTokenValid( 'delete' . $realisation->getId(), $request->request->get( '_token' ) ) ) {
            // delete all related images from the server
            foreach ( $realisation->getImages() as $image ) {
                $name = $image->getName();
                unlink( $this->getParameter( 'realisation_img_dir' ) . '/' . $name );
            }

            $realisationRepository->remove( $realisation, true );

            $this->addFlash( 'success', 'La réalisation a bien été supprimée' );
        }

        return $this->redirectToRoute( 'app_admin_realisation_index', [], Response::HTTP_SEE_OTHER );
    }

    #[Route( '/{id<\d+>}/delete-image', name: 'delete_image', methods: ['DELETE'] )]
    public function deleteImage( ImageRealisation $imageRealisation, Request $request, EntityManagerInterface $entityManager ) : JsonResponse
    {
        $data = json_decode( $request->getContent(), true );

        if ( $this->isCsrfTokenValid( 'delete' . $imageRealisation->getId(), $data['_token'] ) ) {
            $name = $imageRealisation->getName();
            unlink( $this->getParameter( 'realisation_img_dir' ) . '/' . $name );

            $entityManager->remove( $imageRealisation );
            $entityManager->flush();

            return new JsonResponse( ['success' => 1] );
        } else {
            return new JsonResponse( ['error' => 'Token Invalide'], 400 );
        }
    }

    /**
     * @param FormInterface $form
     * @param Realisation $realisation
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function uploadImagesForRealisation( FormInterface $form, Realisation $realisation, EntityManagerInterface $entityManager ) : void
    {
        $uploadImages = $form->get( 'images' )->getData();

        foreach ( $uploadImages as $uploadImage ) {
            $fileName = md5( uniqid() ) . '.' . $uploadImage->guessExtension();
            $uploadImage->move(
                $this->getParameter( 'realisation_img_dir' ),
                $fileName
            );

            $image = new ImageRealisation();
            $image->setName( $fileName );
            $realisation->addImage( $image );

            $entityManager->persist( $image );
        }
    }

}
