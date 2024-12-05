<?php

namespace App\Http\Api\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Entity\QuizResult;
use App\Domain\Quiz\Repository\QuizRepository;
use App\Domain\Quiz\Repository\QuizResultRepository;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quiz', name: 'quiz_')]
class QuizController extends AbstractController
{
    public function __construct(
        private readonly QuizRepository $quizRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly QuizResultRepository $quizResultRepository
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

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function completeQuiz($id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        /** @var Quiz|null $quiz */
        $quiz = $this->quizRepository->find($id);
        if (!$quiz) {
            return $this->json(['error' => 'Quiz not found'], 404);
        }

        // Vérifier si le quiz n'est pas déjà complété
        $existingResult = $this->quizResultRepository->findOneBy(['user' => $user, 'quiz' => $quiz]);
        if ($existingResult) {
            return $this->json(['message' => 'Quiz already completed'], 200);
        }

        $quizResult = new QuizResult();
        $quizResult->setUser($user);
        $quizResult->setQuiz($quiz);
        $quizResult->setScore(0); // Score par défaut à 0 si non soumis
        $quizResult->setCompletedAt(new \DateTime());

        $this->entityManager->persist($quizResult);
        $this->entityManager->flush();

        return $this->json(['message' => 'Quiz marked as completed'], 200);
    }

    #[Route('/{id}/submit', name: 'submit', methods: ['POST'])]
    public function submitQuiz($id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        /** @var Quiz|null $quiz */
        $quiz = $this->quizRepository->find($id);
        if (!$quiz) {
            return $this->json(['error' => 'Quiz not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $score = $data['score'] ?? null;

        if ($score === null) {
            return $this->json(['error' => 'Score is required'], 400);
        }

        // Vérifier si le quiz n'a pas déjà été soumis
        $existingResult = $this->quizResultRepository->findOneBy(['user' => $user, 'quiz' => $quiz]);
        if ($existingResult) {
            return $this->json(['message' => 'Quiz already submitted'], 200);
        }

        $quizResult = new QuizResult();
        $quizResult->setUser($user);
        $quizResult->setQuiz($quiz);
        $quizResult->setScore($score);
        $quizResult->setCompletedAt(new \DateTime());

        $this->entityManager->persist($quizResult);
        $this->entityManager->flush();

        return $this->json(['message' => 'Score submitted successfully'], 200);
    }

    #[Route('/user/completed-quizzes', name: 'user_completed_quizzes', methods: ['GET'])]
    public function getUserCompletedQuizzes(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $completedQuizzes = $this->quizResultRepository->findBy(['user' => $user]);
        $completedQuizIds = array_map(function (QuizResult $result) {
            return $result->getQuiz()->getId();
        }, $completedQuizzes);

        return $this->json(['completedQuizIds' => $completedQuizIds]);
    }
}
