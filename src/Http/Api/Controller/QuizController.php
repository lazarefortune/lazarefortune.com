<?php

namespace App\Http\Api\Controller;

use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Repository\QuizRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quiz', name: 'quiz_')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly QuizRepository $quizRepository
    ) {}

    #[Route('/{id}', name: 'index', methods: ['GET'])]
    public function index($id): JsonResponse
    {
        /** @var Quiz[] $quizzes */
        $quizzes = $this->quizRepository->findBy([
            'targetContent' => $id,
            'isPublished' => true,
        ]);

        if (!$quizzes) {
            return $this->json(['error' => 'No quizzes found'], 404);
        }

        $data = array_map(function(Quiz $quiz) {
            $questions = $quiz->getQuestions();

            $questionsData = array_map(function($question) {
                return [
                    'position' => $question->getPosition(),
                    'text' => $question->getText(),
                    'type' => $question->getType(),
                    'timeLimit' => $question->getTimeLimit(),
                    'answers' => array_map(function($answer, $index) {
                        return [
                            'id' => $answer['id'],
                            'text' => $answer['text'],
                            'isCorrect' => $answer['isCorrect'],
                            'position' => $answer['position'] ?? $index + 1,
                        ];
                    }, $question->getAnswers(), array_keys($question->getAnswers()))
                ];
            }, $questions);

            return [
                'id' => $quiz->getId(),
                'title' => $quiz->getTitle(),
                'questions' => $questionsData,
            ];
        }, $quizzes);

        return $this->json($data);
    }
}
