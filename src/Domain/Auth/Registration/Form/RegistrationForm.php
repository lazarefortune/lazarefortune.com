<?php

namespace App\Domain\Auth\Registration\Form;

use App\Domain\Auth\Core\Dto\CreateUserDto;
use App\Http\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'email', TextType::class, [
                'label' => 'Email',
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'form-input',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'fullname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'autocomplete' => 'name',
                    'class' => 'form-input',
                ],
                'label_attr' => [
                    'class' => 'label',
                ]
            ] )
            ->add( 'agreeTerms', CheckboxType::class, [
                'constraints' => [
                    new IsTrue( [
                        'message' => 'Vous devez accepter les conditions d\'utilisation',
                    ] ),
                ],
                'label' => false,
                'attr' => [
                    'class' => 'form-checkbox',
                ],
            ] )
            ->add('captcha', CaptchaType::class, [
                'mapped' => false
            ])
            ->add( 'plainPassword', PasswordType::class, [
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-input password-verifier'
                ],
                'label' => 'Mot de passe',
                'label_attr' => [
                    'class' => 'label',
                ],
            ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => CreateUserDto::class,
        ] );
    }
}
