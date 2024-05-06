<?php

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Dto\ContactData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
                        'placeholder' => 'Ex: Proposition de collaboration',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'message', CKEditorType::class,
                [
                    'label' => 'Votre message',
                    'attr' => [
                        'placeholder' => 'Votre message',
                    ],
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'config' => [
                        'uiColor' => '#ffffff',
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
