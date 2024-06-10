<?php

namespace App\Http\Controller;

use App\Domain\AntiSpam\ChallengeGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    public function __construct(
        private readonly ChallengeGenerator $generator
    ) {}

    #[Route( '/captcha', name: 'captcha')]
    public function captcha( Request $request ) : Response
    {
        $key = $request->query->get('challenge', '');
        return $this->generator->generate( $key );
    }
}