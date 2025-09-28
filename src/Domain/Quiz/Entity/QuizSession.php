<?php

namespace App\Domain\Quiz\Entity;

use App\Domain\Auth\Core\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class QuizSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity:User::class)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity:Quiz::class)]
    private Quiz $quiz;

    #[ORM\Column(type:'string', length: 64, unique: true)]
    private string $token;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type:'integer', options: ['default'=>1])]
    private int $attemptNumber = 1;

    #[ORM\Column(type:'integer', options:['default'=>0])]
    private int $timeLimit = 0; // en secondes

    public function __construct(Quiz $quiz, ?User $user, int $timeLimit = 0)
    {
        $this->quiz = $quiz;
        $this->user = $user;
        $this->token = bin2hex(random_bytes(16));
        $this->startedAt = new \DateTimeImmutable();
        $this->timeLimit = $timeLimit;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    public function getAttemptNumber(): int
    {
        return $this->attemptNumber;
    }

    public function setAttemptNumber(int $attemptNumber): self
    {
        $this->attemptNumber = $attemptNumber;
        return $this;
    }

    public function incrementAttemptNumber(): self
    {
        $this->attemptNumber++;
        return $this;
    }
}
