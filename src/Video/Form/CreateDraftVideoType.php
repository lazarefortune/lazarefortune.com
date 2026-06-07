<?php

declare(strict_types=1);

namespace App\Video\Form;

use App\Content\Enum\ContentLevel;
use App\Video\Dto\CreateDraftVideoInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CreateDraftVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'ds-input',
                    'placeholder' => 'Ex. Introduction au routing Symfony',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire.'),
                    new Length(max: 255),
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => false,
                'help' => 'Laisse vide pour générer automatiquement depuis le titre.',
                'attr' => [
                    'class' => 'ds-input',
                    'placeholder' => 'introduction-au-routing-symfony',
                ],
                'constraints' => [
                    new Length(max: 255),
                ],
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'Extrait',
                'required' => false,
                'attr' => [
                    'class' => 'ds-input min-h-24',
                    'placeholder' => 'Resume court affiche dans les listes.',
                    'rows' => 4,
                ],
            ])
            ->add('level', EnumType::class, [
                'class' => ContentLevel::class,
                'label' => 'Niveau',
                'required' => false,
                'placeholder' => 'Non défini',
                'choice_label' => static fn (ContentLevel $level): string => match ($level) {
                    ContentLevel::BEGINNER => 'Débutant',
                    ContentLevel::INTERMEDIATE => 'Intermédiaire',
                    ContentLevel::ADVANCED => 'Avancé',
                },
                'attr' => [
                    'class' => 'ds-input',
                ],
            ])
            ->add('coverImagePath', TextType::class, [
                'label' => 'Chemin de couverture',
                'required' => false,
                'help' => 'Chemin relatif ou URL déjà hébergée. Upload à venir.',
                'attr' => [
                    'class' => 'ds-input',
                    'placeholder' => 'covers/ma-video.jpg',
                ],
                'constraints' => [
                    new Length(max: 512),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateDraftVideoInput::class,
        ]);
    }
}
