<?php

declare(strict_types=1);

namespace App\Video\Form;

use App\Video\Dto\UpdateVideoSourceInput;
use App\Video\Enum\VideoVisibility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UpdateVideoSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sourceRef', TextType::class, [
                'label' => 'URL ou identifiant YouTube',
                'help' => 'Ex. dQw4w9WgXcQ ou https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'empty_data' => '',
                'attr' => [
                    'class' => 'ds-input',
                    'placeholder' => 'https://www.youtube.com/watch?v=...',
                    'data-testid' => 'studio-video-source-ref',
                ],
                'constraints' => [
                    new NotBlank(message: 'La reference source est obligatoire.'),
                ],
            ])
            ->add('visibility', EnumType::class, [
                'class' => VideoVisibility::class,
                'label' => 'Visibilite sur YouTube',
                'choice_label' => static fn (VideoVisibility $visibility): string => match ($visibility) {
                    VideoVisibility::PRIVATE => 'Privee',
                    VideoVisibility::UNLISTED => 'Non repertoriee',
                    VideoVisibility::PUBLIC => 'Publique',
                },
                'attr' => [
                    'class' => 'ds-input',
                    'data-testid' => 'studio-video-source-visibility',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateVideoSourceInput::class,
        ]);
    }
}
