<?php

namespace App\Domain\Quiz\Service;

use App\Domain\Quiz\Entity\Quiz;
use App\Domain\Quiz\Entity\QuizSession;

class QuizValidationService
{
    public function validateAnswers(Quiz $quiz, array $userAnswers, QuizSession $session): array
    {
        $score = 0;
        $questions = $quiz->getQuestions();
        $totalQuestions = count($questions);
        $detailedResults = [];

        foreach ($questions as $index => $question) {
            $correctAnswers = array_filter($question->getAnswers(), fn($a) => $a['isCorrect']);
            $correctIds = array_map(fn($a) => $a['id'], $correctAnswers);
            $selected = $userAnswers[$index] ?? [];

            // Validation stricte des réponses
            $isCorrect = $this->validateAnswer($correctIds, $selected, $question->getType());

            if ($isCorrect) {
                $score++;
            }

            $detailedResults[] = [
                'questionIndex' => $index,
                'selectedAnswers' => $selected,
                'correctAnswers' => $correctIds,
                'isCorrect' => $isCorrect,
                'questionType' => $question->getType()
            ];
        }

        $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;

        return [
            'score' => $score,
            'totalQuestions' => $totalQuestions,
            'percentage' => round($percentage, 2),
            'detailedResults' => $detailedResults,
            'sessionAttempt' => $session->getAttemptNumber()
        ];
    }

    private function validateAnswer(array $correctIds, array $selectedIds, string $questionType): bool
    {
        // Vérifier que les IDs sélectionnés sont valides (pas de manipulation)
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

    public function validateTimeConstraints(QuizSession $session, array $questionTimes): bool
    {
        // Vérifier que le temps total ne dépasse pas une limite raisonnable
        $totalTime = array_sum($questionTimes);
        $maxAllowedTime = $session->getTimeLimit() ?: 3600; // 1 heure par défaut

        return $totalTime <= $maxAllowedTime;
    }

    public function detectSuspiciousActivity(array $questionTimes, int $score, int $totalQuestions): array
    {
        $suspiciousFlags = [];

        // Détecter des temps de réponse trop courts (possible triche)
        $avgTimePerQuestion = array_sum($questionTimes) / count($questionTimes);
        if ($avgTimePerQuestion < 2) { // Moins de 2 secondes par question
            $suspiciousFlags[] = 'very_fast_responses';
        }

        // Détecter un score parfait avec des temps très courts
        if ($score === $totalQuestions && $avgTimePerQuestion < 5) {
            $suspiciousFlags[] = 'perfect_score_too_fast';
        }

        // Détecter des patterns de temps identiques (possible script)
        $uniqueTimes = array_unique($questionTimes);
        if (count($uniqueTimes) === 1 && count($questionTimes) > 3) {
            $suspiciousFlags[] = 'identical_response_times';
        }

        return $suspiciousFlags;
    }
}

