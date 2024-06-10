<?php

namespace App\Domain\AntiSpam;

use Symfony\Component\HttpFoundation\Response;

interface ChallengeGenerator
{
    public function generate( string $key ) : Response;
}