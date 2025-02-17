<?php

namespace App\Domain\Collaboration\Entity;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Collaboration\Enum\CollaborationRequestStatus;
use App\Domain\Collaboration\Enum\CollaborationRequestRole;
use App\Domain\Collaboration\Repository\CollaborationRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollaborationRequestRepository::class)]
class CollaborationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $requester;

    #[ORM\Column( type: Types::STRING, length: 255, enumType: CollaborationRequestRole::class )]
    private CollaborationRequestRole $roleRequested;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::STRING, enumType: CollaborationRequestStatus::class, options: ["default" => CollaborationRequestStatus::PENDING])]
    private CollaborationRequestStatus $status = CollaborationRequestStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;


    public function __construct(User $requester)
    {
        $this->requester = $requester;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = CollaborationRequestStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequester(): User
    {
        return $this->requester;
    }

    public function setRequester(User $requester): self
    {
        $this->requester = $requester;
        return $this;
    }

    public function getRoleRequested(): CollaborationRequestRole
    {
        return $this->roleRequested;
    }

    public function setRoleRequested( CollaborationRequestRole $roleRequested): self
    {
        $this->roleRequested = $roleRequested;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getStatus(): CollaborationRequestStatus
    {
        return $this->status;
    }

    public function setStatus(CollaborationRequestStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === CollaborationRequestStatus::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === CollaborationRequestStatus::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === CollaborationRequestStatus::REJECTED;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
