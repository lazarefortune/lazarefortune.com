<?php

namespace App\Helper;

use Symfony\Component\Form\DataTransformerInterface;

class MinutesToTimeHelper implements DataTransformerInterface
{
    public function transform( $timeValue ) : float|int
    {
        if ( null === $timeValue ) {
            return 0;
        }

        $hours = (int)$timeValue->format( 'H' );
        $minutes = (int)$timeValue->format( 'i' );

        return ( $hours * 60 ) + $minutes;
    }

    public function reverseTransform( $minutesValue ) : ?\DateTime
    {
        if ( null === $minutesValue ) {
            return null;
        }

        $timeValue = new \DateTime();
        $hours = floor( $minutesValue / 60 );
        $minutes = $minutesValue % 60;

        $timeValue->setTime( $hours, $minutes );

        return $timeValue;
    }
}
