<?php

namespace App\Domain\Prestation;

use App\Domain\Prestation\Entity\Prestation;
use App\Domain\Prestation\Repository\PrestationRepository;

class PrestationService
{
    public function __construct( private readonly PrestationRepository $prestationRepository )
    {
    }


    public function save( Prestation $prestation ) : void
    {

        foreach ( $prestation->getTags() as $tag ) {
            if ( !$tag->getPrestations()->contains( $prestation ) ) {
                $tag->addPrestation( $prestation );
            }
        }

        if ( !$prestation->isConsiderChildrenForPrice() ) {
            $prestation->setChildrenAgeRange( null );
            $prestation->setChildrenPricePercentage( null );
        }

        $this->prestationRepository->save( $prestation, true );
    }

    /**
     * @return array<Prestation>
     */
    public function getAll() : array
    {
        return $this->prestationRepository->findAll();
    }

    public function getPrestationById( int $prestationId )
    {
        return $this->prestationRepository->find( $prestationId );
    }

}