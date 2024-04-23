<?php

namespace App\Domain\Application\Form;

use App\Domain\Application\Model\WelcomeModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WelcomeForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'siteTitle', TextType::class, [
                'label' => WelcomeModel::SITE_TITLE_LABEL,
                'attr' => [
                    'placeholder' => 'Nom du site',
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'fullname', TextType::class, [
                'label' => 'Votre nom complet',
                'attr' => [
                    'placeholder' => 'Nom complet',
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'username', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'Adresse email',
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn-md btn-primary',
                ],
            ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => WelcomeModel::class,
        ] );
    }
}