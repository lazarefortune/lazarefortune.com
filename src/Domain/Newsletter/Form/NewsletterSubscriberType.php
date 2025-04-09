<?php

namespace App\Domain\Newsletter\Form;

use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Http\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;

class NewsletterSubscriberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Ton prénom',
                    'class' => 'form-input',
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Ton email',
                    'class' => 'form-input'
                ]
            ])
            ->add('hp', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Blank([
                        'message' => 'Votre soumission a été identifiée comme du spam.'
                    ])
                ],
                'attr' => [
                    'style' => 'display:none'
                ],
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewsletterSubscriber::class,
        ]);
    }
}
