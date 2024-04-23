<?php

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Dto\ContactData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'name', TextType::class,
                [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'John Doe',
                        'class' => 'form-input-md',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'email', EmailType::class,
                [
                    'label' => 'Email',
                    'attr' => [
                        'placeholder' => 'johndoe@gmail.com',
                        'class' => 'form-input-md',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'subject', TextType::class,
                [
                    'label' => 'C\'est à propos de ?',
                    'attr' => [
                        'placeholder' => 'Ex: Problème de connexion',
                        'class' => 'form-input-md',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'message', TextareaType::class,
                [
                    'label' => 'Expliquez-nous en détail',
                    'attr' => [
                        'placeholder' => 'Votre message',
                        'class' => 'form-input-md',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => ContactData::class,
        ] );
    }
}
