<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Application\Entity\Content;
use App\Domain\Quiz\Entity\Question;
use App\Domain\Quiz\Entity\Quiz;
use App\Http\Admin\Data\CrudDataInterface;
use App\Http\Form\AutomaticForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class QuizCrudData implements CrudDataInterface
{
    #[Assert\NotBlank]
    public ?string $title;

    #[Assert\NotBlank]
    public ?Content $targetContent;

    public bool $isPublished = false;

    public array $questions = [];

    private EntityManagerInterface $em;

    public function __construct(private readonly Quiz $entity)
    {
        $this->title = $entity->getTitle();
        $this->targetContent = $entity->getTargetContent();
        $this->isPublished = $entity->isPublished();
//        $this->questions = $entity->getQuestions();
        $this->questions = array_map(fn($q) => $q->toArray(), $entity->getQuestions());
    }

    public function getEntity(): Quiz
    {
        return $this->entity;
    }

    public function getFormClass(): string
    {
        return AutomaticForm::class;
    }

    public function hydrate(): void
    {
        $this->entity->setTitle($this->title);
        $this->entity->setTargetContent($this->targetContent);
        $this->entity->setPublished($this->isPublished);
        $questions = $this->createQuestionsFromData($this->questions);

        // Passer les objets Question à setQuestions()
        $this->entity->setQuestions($questions);
        $this->entity->setCreatedAt(new \DateTime());
    }

    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    /**
     * Convertit les données du formulaire en objets Question
     *
     * @param array $questionsData
     * @return Question[]
     */
    private function createQuestionsFromData(array $questionsData): array
    {
        $questions = [];
        $position = 1;

        foreach ($questionsData as $qData) {
            $question = new Question();
            $question->setText($qData['text'] ?? '');
            $question->setType($qData['type'] ?? 'choice');
            $question->setTimeLimit($qData['timeLimit'] ?? 30);
            $question->setPosition($position++);

            // Gérer les réponses
            $answers = [];
            foreach ($qData['answers'] ?? [] as $aData) {
                $answer = [
                    'id' => $aData['id'] ?? null,
                    'text' => $aData['text'] ?? '',
                    'isCorrect' => $aData['isCorrect'] ?? false,
                ];
                $answers[] = $answer;
            }
            $question->setAnswers($answers);

            $questions[] = $question;
        }

        return $questions;
    }
}
