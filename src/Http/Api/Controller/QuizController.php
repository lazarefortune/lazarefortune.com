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
            return $this->json(['error' => 'No quizzes found'], 204);
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
                            'id' => $answer['id'] ?? 'answer_' . $index,
                            'text' => $answer['text'],
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

    #[Route('/validate-answer', name: 'validate_answer', methods: ['POST'])]
    public function validateAnswer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $quizId = $data['quizId'] ?? null;
        $questionIndex = $data['questionIndex'] ?? null;
        $selectedAnswers = $data['selectedAnswers'] ?? [];
        $questionTime = $data['questionTime'] ?? 0;

        if (!$quizId || $questionIndex === null) {
            return $this->json(['error' => 'Missing required parameters'], 400);
        }

        $quiz = $this->quizRepository->find($quizId);
        if (!$quiz || !$quiz->isPublished()) {
            return $this->json(['error' => 'Quiz not found'], 404);
        }

        $questions = $quiz->getQuestions();
        if (!isset($questions[$questionIndex])) {
            return $this->json(['error' => 'Question not found'], 404);
        }

        $question = $questions[$questionIndex];
        $answers = $question->getAnswers();

        // Trouver les bonnes réponses
        $correctAnswers = array_filter($answers, fn($a) => $a['isCorrect']);
        $correctIds = array_map(fn($a) => $a['id'], $correctAnswers);

        // S'assurer que correctIds est un tableau
        $correctIds = array_values($correctIds);

        // Valider la réponse
        $isCorrect = $this->validateAnswerLogic($correctIds, $selectedAnswers, $question->getType());

        // Générer une explication
        $explanation = $this->generateExplanation($question, $correctIds, $selectedAnswers, $isCorrect);

        return $this->json([
            'isCorrect' => $isCorrect,
            'correctAnswers' => $correctIds,
            'explanation' => $explanation,
            'questionTime' => $questionTime
        ]);
    }

    private function validateAnswerLogic(array $correctIds, array $selectedIds, string $questionType): bool
    {
        if (empty($selectedIds)) {
            return false;
        }

        // Pour les questions à choix unique
        if ($questionType === 'choice') {
            return count($selectedIds) === 1 &&
                   count($correctIds) === 1 &&
                   in_array($selectedIds[0], $correctIds);
        }

        // Pour les questions à choix multiples
        if ($questionType === 'multiple_choice') {
            return count($correctIds) === count($selectedIds) &&
                   empty(array_diff($correctIds, $selectedIds)) &&
                   empty(array_diff($selectedIds, $correctIds));
        }

        return false;
    }

    private function generateExplanation($question, array $correctIds, array $selectedIds, bool $isCorrect): string
    {
        if ($isCorrect) {
            return "Excellente réponse ! Vous avez trouvé la bonne solution.";
        } else {
            $correctCount = count($correctIds);
            $selectedCount = count($selectedIds);

            if ($selectedCount === 0) {
                return "Aucune réponse sélectionnée. La bonne réponse était : " . implode(', ', $correctIds);
            } elseif ($selectedCount < $correctCount) {
                return "Réponse incomplète. Il y avait " . $correctCount . " bonne(s) réponse(s) à sélectionner.";
            } elseif ($selectedCount > $correctCount) {
                return "Trop de réponses sélectionnées. Il n'y avait que " . $correctCount . " bonne(s) réponse(s).";
            } else {
                return "Mauvaise réponse. La bonne réponse était : " . implode(', ', $correctIds);
            }
        }
    }
}
