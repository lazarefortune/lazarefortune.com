<?php

namespace App\Domain\Quiz;

use App\Domain\Quiz\Entity\Question;
use Doctrine\ORM\Mapping as ORM;

trait QuestionableTrait
{
    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private array $questions = [];

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        return Question::makeFromQuiz($this);
    }

    public function getRawQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @param Question[] $questions
     */
    public function setQuestions(array $questions): self
    {
        $this->questions = array_map(fn (Question $question) => $question->toArray(), $questions);

        return $this;
    }

    public function setRawQuestions(array $questions): self
    {
        $this->questions = $questions;

        return $this;
    }
}