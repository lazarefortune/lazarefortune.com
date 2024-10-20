<?php

namespace App\Domain\Auth\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class UpdatePasswordForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'currentPassword', PasswordType::class,
                [
                    'label' => 'Mot de passe actuel',
                    'label_attr' => [
                        'class' => 'label'
                    ],
                    'attr' => [
                        'class' => 'form-input'
                    ]
                ] )
            ->add( 'newPassword', PasswordType::class,
                [
                    'label' => 'Nouveau mot de passe',
                    'label_attr' => [
                        'class' => 'label'
                    ],
                    'attr' => [
                        'class' => 'form-input'
                    ]
                ] );
    }
}
