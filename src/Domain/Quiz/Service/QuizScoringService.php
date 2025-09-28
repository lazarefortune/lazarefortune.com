<?php

namespace App\Domain\Quiz\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Entity\QuizSession;
use App\Domain\Badge\Service\BadgeManager;
use Doctrine\ORM\EntityManagerInterface;

class QuizScoringService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BadgeManager $badgeManager,
        private LevelCalculator $levelCalculator
    ) {}

    /**
     * @param Quiz         $quiz
     * @param User|null    $user
     * @param array        $userAnswers ex: [questionIndex => [arrayOfSelectedAnswerIds], ...]
     * @param QuizSession  $session
     */
    public function finalizeQuiz(Quiz $quiz, ?User $user, array $userAnswers, QuizSession $session): array
    {
        $score = 0;
        $questions = $quiz->getQuestions();
        $totalQuestions = count($questions);

        foreach ($questions as $index => $question) {
            $correctAnswers = array_filter($question->getAnswers(), fn($a) => $a['isCorrect']);
            $correctIds = array_map(fn($a) => $a['id'], $correctAnswers);
            $selected = $userAnswers[$index] ?? [];

            $isCorrect = (count($correctIds) === count($selected))
                && empty(array_diff($correctIds, $selected));

            if ($isCorrect) {
                $score++;
            }
        }

        // Pourcentage
        $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;

        // XP
        $xpGained = 0;
        if ($percentage >= 80) {
            $xpGained = 100;
        } elseif ($percentage >= 50) {
            $xpGained = 50;
        } else {
            $xpGained = 20;
        }

        // Bonus première tentative
        if ($session->getAttemptNumber() === 1) {
            $xpGained += 20;
        }

        // Pénalité si ce n'est pas la première
        if ($session->getAttemptNumber() > 1) {
            // ex: -10% par tentative supplémentaire
            $penaltyFactor = 1 - (0.1 * ($session->getAttemptNumber() - 1));
            $xpGained = (int)($xpGained * $penaltyFactor);
        }

        // Maj user (si connecté)
        if ($user) {
            $user->addXp($xpGained);
            $user->incrementQuizzesCompleted();

            // Old/new level
            $oldLevel = $this->levelCalculator->getLevelForXp($user->getXp());
            $this->em->flush(); // flush user xp
            $newLevel = $this->levelCalculator->getLevelForXp($user->getXp());

            if ($newLevel > $oldLevel) {
                // Notification + log possible
            }

            // Vérifier badges
            $this->badgeManager->checkAndUnlockBadges($user, 'quiz_completed', $user->getQuizzesCompleted());

            $this->em->persist($user);
            $this->em->flush();
        }

        return [
            'score' => $score,
            'percentage' => $percentage,
            'xpGained' => $xpGained,
        ];
    }
}
