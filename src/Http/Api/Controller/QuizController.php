<?php

namespace App\Http\Api\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quiz', name: 'quiz_')]
class QuizController extends AbstractController
{
//    public function __construct(
//        private readonly QuizService $quizService
//    ) {}

    #[Route('', methods: ['GET'])]
    public function getQuizzes(): JsonResponse
    {
        $quizzes = [
            [
                'id' => 1,
                'question' => 'Quelle est la couleur du cheval blanc de Napoléon ?',
                'options' => [
                    [
                        'id' => '1_1', // ID unique basé sur la question 1 et l'option 1
                        'text' => 'Blanc',
                        'isCorrect' => true,
                    ],
                    [
                        'id' => '1_2',
                        'text' => 'Noir',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '1_3',
                        'text' => 'Rouge',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '1_4',
                        'text' => 'Vert',
                        'isCorrect' => false,
                    ],
                ],
            ],
            [
                'id' => 2,
                'question' => 'Combien de fois la France a gagné la coupe du monde de football ?',
                'options' => [
                    [
                        'id' => '2_1',
                        'text' => '1',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '2_2',
                        'text' => '2',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '2_3',
                        'text' => '3',
                        'isCorrect' => true,
                    ],
                    [
                        'id' => '2_4',
                        'text' => '4',
                        'isCorrect' => false,
                    ],
                ],
            ],
            [
                'id' => 3,
                'question' => 'Quelle est la capitale de la France ?',
                'options' => [
                    [
                        'id' => '3_1',
                        'text' => 'Paris',
                        'isCorrect' => true,
                    ],
                    [
                        'id' => '3_2',
                        'text' => 'Londres',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '3_3',
                        'text' => 'Madrid',
                        'isCorrect' => false,
                    ],
                    [
                        'id' => '3_4',
                        'text' => 'Berlin',
                        'isCorrect' => false,
                    ],
                ],
            ]
        ];

        return $this->json($quizzes);
    }

}