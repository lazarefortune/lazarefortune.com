<?php

namespace App\Domain\Auth\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => ['attr' => ['class' => 'form-input-lg']],
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'label_attr' => ['class' => 'label']
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'label_attr' => ['class' => 'label']
                ],
                'constraints' => [
                    new NotBlank( ['message' => 'Veuillez entrer un mot de passe'] ),
                    new Length( [
                        'min' => 4,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ] ),
                ],
            ] );
    }
}