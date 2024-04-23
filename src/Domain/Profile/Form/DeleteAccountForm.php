<?php

namespace App\Domain\Profile\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteAccountForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->add('password', PasswordType::class, [
            'label' => 'Mot de passe actuel',
            'attr' => [
                'class' => 'form-input-md'
            ],
            'label_attr' => [
                'class' => 'label'
            ]
        ]);
    }
}