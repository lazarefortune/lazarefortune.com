<?php

namespace App\Domain\Collaboration\Exception;

class AlreadyExistCollaborationRequestException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Demande de collaboration déjà existante');
    }
}