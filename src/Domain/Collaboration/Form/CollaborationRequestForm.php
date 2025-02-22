<?php

namespace App\Domain\Collaboration\Form;

use App\Domain\Collaboration\Entity\CollaborationRequest;
use App\Domain\Collaboration\Enum\CollaborationRequestRole;
use App\Http\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollaborationRequestForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add('roleRequested', ChoiceType::class, [
                'label' => 'Quel rôle souhaitez-vous obtenir ?',
                'choices' => CollaborationRequestRole::choices(),
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'required' => true,
                'attr' => [
                    'placeholder' => 'N\'hésitez pas à en dire plus sur vous, vos motivations et vos compétences',
                    'minlength' => 10,
                    'rows' => 6,
                    'class' => 'form-input',
                ],
            ])->add('captcha', CaptchaType::class, [
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => CollaborationRequest::class,
        ]);
    }

}