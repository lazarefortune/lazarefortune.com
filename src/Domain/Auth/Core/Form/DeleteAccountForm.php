<?php

namespace App\Domain\Auth\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteAccountForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->add('password', PasswordType::class, [
            'label' => 'Votre mot de passe',
            'attr' => [
                'class' => 'form-input'
            ],
            'label_attr' => [
                'class' => 'label'
            ]
        ]);
    }
}