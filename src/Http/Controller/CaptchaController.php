<?php

namespace App\Http\Controller;

use App\Domain\AntiSpam\ChallengeGenerator;
use App\Domain\AntiSpam\Exception\TooManyTryException;
use App\Domain\AntiSpam\Puzzle\PuzzleChallenge;
use App\Http\DTO\CaptchaGuessDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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

    #[Route('/captcha/validate', methods: ['POST'])]
    public function validate(
        #[MapRequestPayload] CaptchaGuessDTO $guess,
        PuzzleChallenge $keyService,
    ): Response {
        try {
            $isValid = $keyService->verifyKey($guess->key, $guess->response);

            if ($isValid) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            return new Response('{}', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (TooManyTryException $e) {
            // Générer une nouvelle clé si le maximum d'essais est atteint
//            $newKey = $keyService->generateKey();
            return new Response(json_encode(['newKey' => $e->getNewKey()]), Response::HTTP_FORBIDDEN);
        }
    }
}