<?php

namespace App\Domain\Quiz\Entity;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Quiz\QuestionableTrait;
use App\Domain\Quiz\Repository\QuizRepository;
use App\Domain\Quiz\Repository\QuizResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    use QuestionableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Content::class, inversedBy: "quizzes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Content $targetContent = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->title = '';
        $this->isPublished = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTargetContent(): ?Content
    {
        return $this->targetContent;
    }

    public function setTargetContent(Content $targetContent): self
    {
        $this->targetContent = $targetContent;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isCompletedByUser(User $user, QuizResultRepository $quizResultRepository): bool
    {
        $result = $quizResultRepository->findOneBy(['user' => $user, 'quiz' => $this]);
        return $result !== null;
    }
}
