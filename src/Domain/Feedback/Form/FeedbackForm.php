<?php

namespace App\Domain\Feedback\Form;

use App\Domain\Feedback\Entity\Feedback;
use App\Http\Type\CaptchaType;
use App\Http\Type\DropZoneFileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;


class FeedbackForm extends AbstractType
{
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $isUser = $user !== null;


        $builder
            ->add('message', TextareaType::class, [
                'label' => 'Donne-nous un maximum de détails 💡',
                'required' => true,
                'constraints' => [new Assert\NotBlank()],
                'attr' => [
                    'rows' => 6,
                    'class' => 'form-input',
                ],
            ])
            ->add('imageFile', DropZoneFileType::class, [
                'label' => 'Ajoute une image ou un exemple (facultatif)',
                'required' => false,
            ]);

        if (!$isUser) {
            $builder
                ->add('firstName', TextType::class, [
                    'label' => 'Ton prénom',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank()],
                    'attr' => [
                        'class' => 'form-input',
                    ]
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Ton e-mail',
                    'required' => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                    'attr' => [
                        'class' => 'form-input',
                    ]
                ])
                ->add('captcha', CaptchaType::class, [
                    'mapped' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
