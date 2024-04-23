<?php

namespace App\Http\Api\Controller;

use App\Domain\Application\Entity\Option;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiOffDaysController extends AbstractController
{
    public function __construct( private readonly EntityManagerInterface $entityManager )
    {
    }

    #[Route( '/off-days', name: 'off_days_' )]
    public function index() : JsonResponse
    {
        $openDaysOption = $this->entityManager->getRepository( Option::class )->findOneBy( ['name' => 'open_days'] );

        if ( null === $openDaysOption ) {
            return new JsonResponse( [] );
        }

        $openDaysString = $openDaysOption->getValue();

        $openDaysArray = explode( ',', $openDaysString );

        return new JsonResponse( $openDaysArray );
    }
}