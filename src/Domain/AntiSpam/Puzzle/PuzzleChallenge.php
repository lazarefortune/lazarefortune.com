<?php

namespace App\Domain\AntiSpam\Puzzle;

use App\Domain\AntiSpam\ChallengeInterface;
use App\Domain\AntiSpam\Exception\TooManyTryException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PuzzleChallenge implements ChallengeInterface
{
    public const WIDTH = 350;
    public const HEIGHT = 200;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'CAPTCHA';
    private const PRECISION = 5;

    private const MARGIN_OF_ERROR = 5;
    private const SESSION_KEY_TRIES = 'CAPTCHA_TRIES';
    private const MAX_TRY = 3;

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

    public function verifyKey(string $key, string $answer): bool
    {
        // Récupérer la solution attendue
        $expected = $this->getSolution($key);
        if (!$expected) {
            return false;
        }

        // Récupérer la session et les tentatives associées
        $session = $this->getSession();
        $triesSession = $session->get(self::SESSION_KEY_TRIES, []);

        // Trouver les tentatives pour cette clé
        $currentTries = 0;
        foreach ($triesSession as $entry) {
            if ($entry["key"] === $key) {
                $currentTries = $entry["tries"];
                break;
            }
        }

        // Convertir la réponse en coordonnées
        $got = $this->stringToPosition($answer);
        $isCorrect = abs($expected[0] - $got[0]) < self::PRECISION && abs($expected[1] - $got[1]) < self::PRECISION;

        // Si la tentative échoue
        if (!$isCorrect) {
            // Incrémenter les tentatives pour cette clé
            $found = false;
            foreach ($triesSession as &$entry) {
                if ($entry["key"] === $key) {
                    $entry["tries"]++;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $triesSession[] = ['key' => $key, 'tries' => 1];
            }

            // Vérifier si le nombre maximum de tentatives est atteint
            if ($currentTries + 1 >= self::MAX_TRY) {
                // Retirer l'ancien puzzle et générer une nouvelle clé
                $puzzles = $session->get(self::SESSION_KEY, []);
                $session->set(self::SESSION_KEY, array_filter($puzzles, fn(array $puzzle) => $puzzle['key'] !== intval($key)));

                $newKey = $this->generateKey();

                // Mettre à jour la session
                $session->set(self::SESSION_KEY_TRIES, array_filter($triesSession, fn($entry) => $entry["key"] !== $key));
                $session->save();

                throw new TooManyTryException($newKey);
            }

            // Mettre à jour la session
            $session->set(self::SESSION_KEY_TRIES, $triesSession);
            $session->save();
        }

        return $isCorrect;
    }

    public function verifyKeyOld(string $guessKey): bool
    {
        $guess = array_map(fn (string $v) => intval($v), explode('-', $guessKey));

        $key = $this->getSession()->get(self::SESSION_KEY);
        if ($key === null) {
            return false;
        }
        $keyLength = count($key);
        for ($i = 0; $i < $keyLength; ++$i) {
            // Le nombre est trop petit ou trop grand
            $min = $key[$i] - self::MARGIN_OF_ERROR;
            $max = $key[$i] + self::MARGIN_OF_ERROR;
            if ($guess[$i] < $min || $guess[$i] > $max) {
                $session = $this->getSession();
                $tries = ($session->get(self::SESSION_KEY_TRIES) ?? 0) + 1;
                if ($tries >= self::MAX_TRY) {
                    $this->generateKey();
                    # throw new TooManyTryException();
                }
                $session->set(self::SESSION_KEY_TRIES, $tries);
                $session->save();

                return false;
            }
        }

        return true;
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