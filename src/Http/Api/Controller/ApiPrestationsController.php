<?php

namespace App\Http\Api\Controller;

use App\Domain\Prestation\Entity\Prestation;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiPrestationsController extends AbstractController
{
    public function __construct( private readonly EntityManagerInterface $entityManager )
    {
    }

    #[Route( '/prestations/{id}', name: 'prestations_' )]
    public function index( Prestation $prestation ) : JsonResponse
    {
        $prestations = $this->entityManager->getRepository( Prestation::class )->findBy( ['id' => $prestation->getId()] );

        $apiPrestations = [];
        foreach ( $prestations as $prestation ) {
            $apiPrestations = [
                'id' => $prestation->getId(),
                'timeStart' => $prestation->getStartTime()->format( 'H:i:s' ),
                'timeEnd' => $prestation->getEndTime()->format( 'H:i:s' ),
                'duration' => $prestation->getDuration()->format( 'H:i:s' ),
            ];
        }

        return new JsonResponse( $apiPrestations );
    }
}