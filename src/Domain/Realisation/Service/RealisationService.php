<?php

namespace App\Domain\Realisation\Service;

use App\Domain\Realisation\Entity\Realisation;
use App\Domain\Realisation\Repository\RealisationRepository;

class RealisationService
{

    private RealisationRepository $realisationRepository;

    public function __construct( RealisationRepository $realisationRepository )
    {
        $this->realisationRepository = $realisationRepository;
    }

    /**
     * @return array<Realisation>
     */
    public function getRealisations() : array
    {
        return $this->realisationRepository->findAll();
    }

    public function getCountRealisations() : int
    {
        return $this->realisationRepository->countRealisations();
    }

    public function getRealisation( int $id ) : ?Realisation
    {
        return $this->realisationRepository->find( $id );
    }

    public function save( Realisation $realisation, bool $flush = false ) : void
    {
        $this->realisationRepository->save( $realisation, $flush );
    }

    public function remove( Realisation $realisation, bool $flush = false ) : void
    {
        $this->realisationRepository->remove( $realisation, $flush );
    }

}