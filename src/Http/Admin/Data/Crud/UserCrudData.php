<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Auth\Core\Entity\User;
use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property User $entity
 */
#[Unique( field: 'email' )]
class UserCrudData extends AutomaticCrudData
{
    #[Assert\NotBlank]
    #[Assert\Length( min: 3 )]
    public string $fullname = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\Length( min: 10 )]
    public ?string $phone = '';

    public ?\DateTimeInterface $dateOfBirthday = null;

    public array $roles = [];

    public function hydrate() : void
    {
        parent::hydrate();
        $this->entity->setCgu( true );
        $this->entity->setUpdatedAt( new \DateTimeImmutable() );
        $this->entity->setDateOfBirthday( $this->dateOfBirthday );
        $this->entity->setRoles( $this->roles );
        $this->entity->setPassword( '' );
    }
}