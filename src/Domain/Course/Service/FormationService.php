<?php

namespace App\Domain\Course\Service;

use App\Domain\Course\Repository\FormationRepository;

class FormationService
{
    public function __construct(
        private readonly FormationRepository $formationRepository
    )
    {
    }

    public function getFormations() : array
    {
        return $this->formationRepository->findBy([ 'online' => true ], [ 'createdAt' => 'DESC' ]);
    }
}