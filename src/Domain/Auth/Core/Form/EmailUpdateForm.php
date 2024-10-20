<?php

namespace App\Domain\Auth\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailUpdateForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Nouvelle adresse email'
                ]
            ] );
    }
}