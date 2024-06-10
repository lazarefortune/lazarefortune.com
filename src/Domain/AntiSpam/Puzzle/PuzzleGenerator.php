<?php

namespace App\Domain\AntiSpam\Puzzle;

use App\Domain\AntiSpam\ChallengeGenerator;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class PuzzleGenerator implements ChallengeGenerator
{
    public function __construct( private readonly PuzzleChallenge $challenge, private readonly string $publicPath )
    {
    }

    public function generate( string $key ) : Response
    {
        $position = $this->challenge->getSolution( $key );
        if ( !$position ) return new Response( null, Response::HTTP_NOT_FOUND );

        [$x, $y] = $position;
        $imageName = 'captcha'.rand(1,6).'.png';
        $backgroundPath = sprintf( '%s/images/captcha/%s', $this->publicPath, $imageName );
        $piecePath = sprintf( '%s/images/captcha/hole.png', $this->publicPath );

        $manager = new ImageManager( ['driver' => 'gd'] );
        $image = $manager->make( $backgroundPath );
        $piece = $manager->make( $piecePath );

        $hole = clone $piece;
        $piece->insert( $image, 'top-left', -$x, -$y )
            ->mask( $hole, true );

        $image
            ->resizeCanvas(
                PuzzleChallenge::PIECE_WIDTH,
                0,
                'left',
                true,
                'rgba(0,0,0,0)',
            )
            ->insert( $piece, 'top-right' )
            ->insert( $hole->opacity(90), 'top-left', $x, $y );

        return $image->response( 'png' );
    }
}