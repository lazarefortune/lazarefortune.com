<?php

namespace App\Domain\Quiz\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Entity\QuizSession;
use App\Domain\Quiz\Repository\QuizSessionRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuizSessionManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly QuizSessionRepository $quizSessionRepository
    ) {}

    public function createSession(Quiz $quiz, ?User $user, int $timeLimit = 0): QuizSession
    {
        // Vérifier s'il existe déjà une session active pour cet utilisateur et ce quiz
        if ($user) {
            $existingSession = $this->quizSessionRepository->findOneBy([
                'user' => $user,
                'quiz' => $quiz
            ]);

            if ($existingSession) {
                // Vérifier si la session n'est pas expirée (par exemple, 2 heures)
                $expirationTime = $existingSession->getStartedAt()->modify('+2 hours');
                if (new \DateTimeImmutable() < $expirationTime) {
                    return $existingSession;
                } else {
                    // Supprimer la session expirée
                    $this->em->remove($existingSession);
                    $this->em->flush();
                }
            }
        }

        $session = new QuizSession($quiz, $user, $timeLimit);
        $this->em->persist($session);
        $this->em->flush();

        return $session;
    }

    public function validateSession(string $token, ?User $user = null): ?QuizSession
    {
        $session = $this->quizSessionRepository->findOneBy(['token' => $token]);

        if (!$session) {
            return null;
        }

        // Vérifier l'expiration (2 heures)
        $expirationTime = $session->getStartedAt()->modify('+2 hours');
        if (new \DateTimeImmutable() > $expirationTime) {
            $this->em->remove($session);
            $this->em->flush();
            return null;
        }

        // Vérifier que l'utilisateur correspond (si connecté)
        if ($user && $session->getUser() && $session->getUser()->getId() !== $user->getId()) {
            return null;
        }

        return $session;
    }

    public function incrementAttempt(QuizSession $session): QuizSession
    {
        $session->incrementAttemptNumber();
        $this->em->flush();

        return $session;
    }

    public function isSessionExpired(QuizSession $session): bool
    {
        $expirationTime = $session->getStartedAt()->modify('+2 hours');
        return new \DateTimeImmutable() > $expirationTime;
    }
}

