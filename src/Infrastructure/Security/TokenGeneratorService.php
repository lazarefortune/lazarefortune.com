<?php

namespace App\Infrastructure\Security;

class TokenGeneratorService
{
    public function generate( int $length = 25 ) : string
    {
        $length = max( 2, min( PHP_INT_MAX, $length ) );
        $halfLength = (int)ceil( $length / 2 );

        return substr( bin2hex( random_bytes( $halfLength ) ), 0, $length );
    }
}