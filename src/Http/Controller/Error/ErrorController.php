<?php

namespace App\Http\Controller\Error;

use App\Domain\Course\Repository\CourseRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Génère le body de la page d'erreur.
 */
class ErrorController extends AbstractController
{
    public function body(CourseRepository $courseRepository): Response
    {
        return $this->render('bundles/TwigBundle/Exception/_body.html.twig', [
            'courses' => $courseRepository->findRandom(4),
        ]);
    }
}