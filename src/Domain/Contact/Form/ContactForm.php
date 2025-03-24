<?php

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Dto\ContactData;
use App\Http\Type\CaptchaType;
use App\Http\Type\DropZoneFileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContactForm extends AbstractType
{
    public function __construct( private TokenStorageInterface $tokenStorage )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $isUser = $user !== null;

        if (!$isUser) {
            $builder
                ->add('name', TextType::class, [
                    'label' => 'Ton prénom',
                    'attr' => ['class' => 'form-input'],
                    'label_attr' => ['class' => 'label'],
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Ton adresse e-mail',
                    'attr' => ['class' => 'form-input'],
                    'label_attr' => ['class' => 'label'],
                ])
                ->add('captcha', CaptchaType::class, [
                    'mapped' => false,
                    'route' => 'app_captcha',
                ]);
        }

        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet du message',
                'attr' => ['class' => 'form-input'],
                'label_attr' => ['class' => 'label'],
            ])
            ->add('imageFile', DropZoneFileType::class, [
                'label' => 'Ajoute une capture ou un fichier (facultatif)',
                'required' => false,
                'mapped' => true,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Ton message ✉️',
                'attr' => [
                    'class' => 'form-input',
                    'rows' => 7,
                    'placeholder' => 'Dis-moi tout...'
                ],
                'label_attr' => ['class' => 'label'],
            ]);
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => ContactData::class,
        ] );
    }
}
