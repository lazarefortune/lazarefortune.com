<?php

namespace App\Helper;

use Symfony\Component\Form\DataTransformerInterface;

class CentToEuroTransformer implements DataTransformerInterface
{
    public function transform( $value ) : ?string
    {
        if ( null === $value ) {
            return null;
        }

        return sprintf( '%.2f', $value / 100 );
    }

    public function reverseTransform( $value ) : ?int
    {
        if ( !$value ) {
            return null;
        }

        if ( !is_numeric( $value ) ) {
            throw new \Exception( 'Expected a numeric.' );
        }

        return (int)bcmul( $value, '100', 0 );
    }
}