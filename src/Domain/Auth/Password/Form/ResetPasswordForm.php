<?php

namespace App\Domain\Auth\Password\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => ['attr' => ['class' => 'password-verifier']],
                'required' => true,
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'label_attr' => ['class' => 'label'],
                    'attr' => ['class' => 'form-input'],
                ],@
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'label_attr' => ['class' => 'label'],
                    'attr' => ['class' => 'form-input'],
                ],
                'constraints' => [
                    new NotBlank( ['message' => 'Veuillez entrer un mot de passe'] ),
                    new Length( [
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ] ),
                    new Regex( [
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'message' => 'Votre mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre',
                    ] ),
                    new Regex( [
                        'pattern' => '/[!@#$%^&*(),.?":{}|<>]/',
                        'message' => 'Votre mot de passe doit contenir au moins un caractère spécial',
                    ] ),
                ],
            ] );
    }
}