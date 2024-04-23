<?php

namespace App\Domain\Password\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class UpdatePasswordForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'password', RepeatedType::class, [
                'type' => PasswordType::class,
                'attr' => [
                    'class' => 'form-input-md',
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Nouveau mot de passe',
                        'class' => 'form-input-md',
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Confirmer le nouveau mot de passe',
                        'class' => 'form-input-md',
                    ],
                ],
            ] );
    }
}
