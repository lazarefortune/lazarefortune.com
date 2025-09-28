<?php

namespace App\Http\Api\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Entity\QuizResult;
use App\Domain\Quiz\Repository\QuizRepository;
use App\Domain\Quiz\Repository\QuizResultRepository;
use App\Domain\Quiz\Service\QuizSessionManager;
use App\Domain\Quiz\Service\QuizValidationService;
use App\Domain\Quiz\Service\QuizScoringService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/secure-quiz', name: 'secure_quiz_')]
class SecureQuizController extends AbstractController
{
    public function __construct(
        private readonly QuizRepository $quizRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly QuizResultRepository $quizResultRepository,
        private readonly QuizSessionManager $sessionManager,
        private readonly QuizValidationService $validationService,
        private readonly QuizScoringService $scoringService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly ValidatorInterface $validator,
        private readonly RequestStack $requestStack
    ) {}

    #[Route('/start/{id}', name: 'start', methods: ['POST'])]
    public function startQuiz(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $quiz = $this->quizRepository->find($id);
        if (!$quiz || !$quiz->isPublished()) {
            return $this->json(['error' => 'Quiz not found'], 404);
        }

        // Vérifier si l'utilisateur a déjà complété ce quiz
        if ($user) {
            $existingResult = $this->quizResultRepository->findOneBy(['user' => $user, 'quiz' => $quiz]);
            if ($existingResult) {
                return $this->json(['error' => 'Quiz already completed'], 409);
            }
        }

        // Créer une session sécurisée
        $session = $this->sessionManager->createSession($quiz, $user);

        return $this->json([
            'sessionToken' => $session->getToken(),
            'quizId' => $quiz->getId(),
            'title' => $quiz->getTitle(),
            'timeLimit' => $session->getTimeLimit(),
            'attemptNumber' => $session->getAttemptNumber()
        ]);
    }

    #[Route('/submit', name: 'submit', methods: ['POST'])]
    public function submitQuiz(Request $request): JsonResponse
    {
        // Rate limiting - utiliser le service via le conteneur
        $limiter = $this->container->get('quiz_submission_limiter');
        $rateLimiter = $limiter->create($this->getClientIp());
        if (!$rateLimiter->consume()->isAccepted()) {
            return $this->json(['error' => 'Too many requests'], 429);
        }

        /** @var User|null $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        // Validation des données d'entrée
        $constraints = new Assert\Collection([
            'sessionToken' => [new Assert\NotBlank(), new Assert\Type('string')],
            'answers' => [new Assert\NotBlank(), new Assert\Type('array')],
            'questionTimes' => [new Assert\Type('array')],
            'csrf_token' => [new Assert\NotBlank(), new Assert\Type('string')]
        ]);

        $violations = $this->validator->validate($data, $constraints);
        if (count($violations) > 0) {
            return $this->json(['error' => 'Invalid data', 'violations' => $violations], 400);
        }

        // Vérification CSRF
        if (!$this->csrfTokenManager->isTokenValid($data['csrf_token'])) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        // Validation de la session
        $session = $this->sessionManager->validateSession($data['sessionToken'], $user);
        if (!$session) {
            return $this->json(['error' => 'Invalid or expired session'], 403);
        }

        $quiz = $session->getQuiz();

        // Validation des contraintes de temps
        $questionTimes = $data['questionTimes'] ?? [];
        if (!$this->validationService->validateTimeConstraints($session, $questionTimes)) {
            return $this->json(['error' => 'Time constraints violated'], 400);
        }

        // Détection d'activité suspecte
        $suspiciousFlags = $this->validationService->detectSuspiciousActivity(
            $questionTimes,
            count(array_filter($data['answers'])),
            count($quiz->getQuestions())
        );

        // Validation et calcul du score côté serveur
        $validationResult = $this->validationService->validateAnswers($quiz, $data['answers'], $session);

        // Si activité suspecte détectée, logger et appliquer des sanctions
        if (!empty($suspiciousFlags)) {
            // Log l'activité suspecte
            error_log("Suspicious quiz activity detected for user {$user?->getId()}: " . implode(', ', $suspiciousFlags));

            // Réduire le score en cas d'activité suspecte
            $validationResult['score'] = max(0, $validationResult['score'] - 2);
            $validationResult['suspiciousActivity'] = $suspiciousFlags;
        }

        // Vérifier si le quiz n'a pas déjà été soumis
        if ($user) {
            $existingResult = $this->quizResultRepository->findOneBy(['user' => $user, 'quiz' => $quiz]);
            if ($existingResult) {
                return $this->json(['error' => 'Quiz already submitted'], 409);
            }

            // Créer le résultat du quiz
            $quizResult = new QuizResult();
            $quizResult->setUser($user);
            $quizResult->setQuiz($quiz);
            $quizResult->setScore($validationResult['score']);
            $quizResult->setCompletedAt(new \DateTime());

            $this->entityManager->persist($quizResult);

            // Utiliser le service de scoring pour les récompenses
            $scoringResult = $this->scoringService->finalizeQuiz($quiz, $user, $data['answers'], $session);
            $validationResult['xpGained'] = $scoringResult['xpGained'] ?? 0;
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'score' => $validationResult['score'],
            'totalQuestions' => $validationResult['totalQuestions'],
            'percentage' => $validationResult['percentage'],
            'xpGained' => $validationResult['xpGained'] ?? 0,
            'detailedResults' => $validationResult['detailedResults'],
            'suspiciousActivity' => $suspiciousFlags ?? []
        ]);
    }

    #[Route('/session/{token}', name: 'get_session', methods: ['GET'])]
    public function getSession(string $token): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $session = $this->sessionManager->validateSession($token, $user);
        if (!$session) {
            return $this->json(['error' => 'Invalid or expired session'], 404);
        }

        return $this->json([
            'sessionToken' => $session->getToken(),
            'quizId' => $session->getQuiz()->getId(),
            'startedAt' => $session->getStartedAt()->format('c'),
            'attemptNumber' => $session->getAttemptNumber(),
            'timeLimit' => $session->getTimeLimit()
        ]);
    }

    private function getClientIp(): string
    {
        return $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    }
}
