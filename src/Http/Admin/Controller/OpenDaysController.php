<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Entity\Option;
use App\Domain\Application\Form\OpenDaysForm;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/jours-ouverture', name: 'open_days_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class OpenDaysController extends AbstractController
{
    public function __construct( private readonly EntityManagerInterface $entityManager )
    {
    }

    #[Route( '/', name: 'index' )]
    public function list( Request $request ) : Response
    {
        $repository = $this->entityManager->getRepository( Option::class );
        $option = $repository->findOneBy( ['name' => 'open_days'] );

        if ( null === $option ) {
            $option = new Option( 'Jours d\'ouverture', 'open_days', '', ChoiceType::class );
            $this->entityManager->persist( $option );
            $this->entityManager->flush();
        }

        $openDays = explode( ',', $option->getValue() );

        $form = $this->createForm( OpenDaysForm::class, ['open_days' => $openDays] );

        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $openDays = $form->get( 'open_days' )->getData();
            $openDays = implode( ',', $openDays );
            $option->setValue( $openDays );

            $this->entityManager->persist( $option );
            $this->entityManager->flush();

            $this->addFlash( 'success', 'Les jours d\'ouverture ont été mis à jour.' );

            return $this->redirectToRoute( 'admin_open_days_index' );
        }

        return $this->render( 'admin/open-days/list.html.twig', [
            'form' => $form->createView(),
        ] );
    }

}