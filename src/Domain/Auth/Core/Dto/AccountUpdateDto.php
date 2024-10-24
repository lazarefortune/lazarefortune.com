<?php

namespace App\Domain\Auth\Core\Dto;

use App\Domain\Auth\Core\Entity\User;
use App\Validator\UniqueField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

//#[UniqueField( entityClass: User::class, field: 'email', message: 'Cette adresse email est déjà utilisée' )]
class AccountUpdateDto
{
    #[Assert\NotBlank( message: 'Veuillez renseigner votre nom complet' )]
    #[Assert\Length( min: 3, max: 255, minMessage: 'Votre nom complet doit contenir au moins {{ limit }} caractères', maxMessage: 'Votre nom complet doit contenir au maximum {{ limit }} caractères' )]
    public ?string $fullname = '';

    #[Assert\Length( min: 3, max: 255, minMessage: 'Votre numéro de téléphone doit contenir au moins {{ limit }} caractères', maxMessage: 'Votre numéro de téléphone doit contenir au maximum {{ limit }} caractères' )]
    #[Assert\Regex( pattern: '/^(?:(?:\+33|0)[1-9])[\s.-]?(\d{2}[\s.-]?){4}$/', message: 'Veuillez renseigner un numéro de téléphone valide' )]
    public ?string $phone = '';

    /*
    #[Assert\NotBlank( message: 'Veuillez renseigner votre adresse email' )]
    #[Assert\Email( message: 'Veuillez renseigner une adresse email valide' )]
    #[Assert\Length( min: 3, max: 255, minMessage: 'Votre adresse email doit contenir au moins {{ limit }} caractères', maxMessage: 'Votre adresse email doit contenir au maximum {{ limit }} caractères' )]
    public string $email = '';


    #[Assert\LessThan( value: 'today', message: 'Votre date de naissance doit être inférieure à la date du jour' )]
    public ?\DateTimeInterface $dateOfBirthday = null;
    */

    public User $user;

    public function __construct( User $user )
    {
        $this->user = $user;
        $this->fullname = $user->getFullname();
        /*
        $this->email = $user->getEmail();
        $this->phone = $user->getPhone();
        $this->dateOfBirthday = $user->getDateOfBirthday();
        */
    }

    public function getId() : int
    {
        return $this->user->getId() ? : 0;
    }

    public function hydrate() : void
    {
        $this->user->setFullname( $this->fullname );
        /*
        $this->user->setEmail( $this->email );
        $this->user->setDateOfBirthday( $this->dateOfBirthday );
        */
        $this->user->setPhone( $this->phone );
        $this->user->setUpdatedAt( new \DateTimeImmutable() );
    }

}