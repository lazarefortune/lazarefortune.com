<?php

namespace App\Domain\AntiSpam\Puzzle;

use App\Domain\AntiSpam\ChallengeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PuzzleChallenge implements ChallengeInterface
{
    public const WIDTH = 350;
    public const HEIGHT = 200;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'puzzles';
    private const PRECISION = 2;

    public function __construct( private readonly RequestStack $requestStack )
    {
    }

    public function generateKey() : string
    {
        $session = $this->getSession();
        $now = time();

        // On génère une position pour la pièce
        $x = mt_rand( 0, self::WIDTH - self::PIECE_WIDTH );
        $y = mt_rand( 0, self::HEIGHT - self::PIECE_HEIGHT );

        // On récupère et sauvegarde le problème en session
        $puzzles = $session->get( self::SESSION_KEY, [] );

        $puzzles[] = ['key' => $now, 'solution' => [$x, $y]];
        $session->set( self::SESSION_KEY, array_slice( $puzzles, -10 ) );

        return $now;
    }

    public function verify( string $key, string $answer ) : bool
    {
        $excepted = $this->getSolution( $key );
        if ( !$excepted ) return false;

        // remove puzzle from session
        $session = $this->getSession();
        $puzzles = $session->get( self::SESSION_KEY, [] );
        $session->set( self::SESSION_KEY, array_filter( $puzzles, fn ( array $puzzle ) => $puzzle['key'] !== intval( $key ) ) );

        $got = $this->stringToPosition( $answer );
        return abs( $excepted[0] - $got[0] ) < self::PRECISION && abs( $excepted[1] - $got[1] ) < self::PRECISION;
    }

    /**
     * @param string $key
     * @return int[]|null
     */
    public function getSolution( string $key ) : array|null
    {
        $puzzles = $this->getSession()->get( self::SESSION_KEY, [] );
        foreach ( $puzzles as $puzzle ) {
            if ( $puzzle['key'] !== intval( $key ) ) {
                continue;
            }
            return $puzzle['solution'];
        }
        return null;
    }

    private function getSession() : SessionInterface
    {
        return $this->requestStack->getMainRequest()->getSession();
    }

    /**
     * @param string $s
     * @return int[]
     */
    private function stringToPosition( string $s ) : array
    {
        $parts = explode( '-', $s, 2 );
        if ( count( $parts ) !== 2 ) {
            return [-1, -1];
        }
        return [intval( $parts[0] ), intval( $parts[1] )];
    }
}