<?php

namespace App\Domain\Auth\Core\Dto;

use App\Domain\Auth\Core\Entity\User;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique( field: 'email', entityClass: User::class )]
class CreateUserDto
{
    #[Assert\NotBlank( message: 'Veuillez renseigner votre nom complet' )]
    #[Assert\Length( min: 3, minMessage: 'Votre nom complet doit contenir au moins {{ limit }} caractères' )]
    public string $fullname = '';

    #[Assert\NotBlank( message: 'Veuillez renseigner votre adresse email' )]
    #[Assert\Email( message: 'Veuillez renseigner une adresse email valide' )]
    public string $email = '';

    #[Assert\NotBlank( message: 'Veuillez renseigner votre mot de passe' )]
    #[Assert\Length( min: 6, minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères' )]
    #[Assert\Regex( pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#.^])[A-Za-z\d@$!%*?&#.^]{6,}$/', message: 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial' )]
    public string $plainPassword = '';

    #[Assert\IsTrue( message: 'Vous devez accepter les conditions d\'utilisation' )]
    public bool $agreeTerms = false;

    public function __construct( public User $user )
    {
    }

    public function getId() : ?int
    {
        if ( method_exists( $this->user, 'getId' ) ) {
            return $this->user->getId();
        }
        throw new \RuntimeException( "L'entité doit avoir une méthode getId()" );
    }
}