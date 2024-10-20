<?php

namespace App\Domain\Auth\Password\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;


class ForgotPasswordForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank( [
                        'message' => 'Veuillez saisir votre adresse email',
                    ] ),
                    new Email( [
                        'message' => 'Veuillez saisir une adresse email valide',
                    ] ),
                ]
            ] );
    }
}