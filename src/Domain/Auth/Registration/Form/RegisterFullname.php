<?php

namespace App\Domain\Auth\Registration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterFullname extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add( 'fullname' , TextType::class, [
                'mapped' => false,
                'label' => 'J\'aurais juste besoin de ton prénom',
                'label_attr' => [
                    'class' => 'label'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                    ])
                ],
                'attr' => [
                    'class' => 'form-input',
                    'autocomplete' => 'name'
                ]
            ]);
    }
}