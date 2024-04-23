<?php

namespace App\Domain\Auth\Dto;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class NewUserData
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
}