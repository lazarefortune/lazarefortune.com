<?php

namespace App\Domain\Quiz\Entity;

class Question
{
    private string $text;
    private string $type; // 'choice', 'multiple_choice', 'code'
    private ?int $timeLimit;
    private int $position;
    private array $answers = [];

    public static function makeFromQuiz($quiz): array
    {
        $questions = [];
        foreach ($quiz->getRawQuestions() as $q) {
            $question = new self();
            $question->text = $q['text'];
            $question->type = $q['type'];
            $question->timeLimit = $q['timeLimit'] ?? 30;
            $question->position = $q['position'];
            $question->answers = $q['answers'] ?? [];
            $questions[] = $question;
        }

        return $questions;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'type' => $this->type,
            'timeLimit' => $this->timeLimit,
            'position' => $this->position,
            'answers' => $this->answers,
        ];
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(?int $timeLimit): self
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;

        return $this;
    }
}
