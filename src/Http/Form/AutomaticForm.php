<?php

namespace App\Http\Form;

use App\Http\Type\ChoiceMultipleType;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Génère un formulaire de manière automatique en lisant les propriétés d'un objet.
 */
class AutomaticForm extends AbstractType
{
    final public const TYPES = [
        'string' => TextType::class,
        'bool' => SwitchType::class,
        'int' => NumberType::class,
        'float' => NumberType::class,
        'array' => ChoiceType::class,
        UploadedFile::class => FileType::class,
        \DateTimeInterface::class => DateType::class,
    ];

    final public const NAMES = [
        'content' => TextareaType::class,
        'description' => TextareaType::class,
        'short' => TextareaType::class,
        'color' => ColorType::class,
        'links' => TextareaType::class,
    ];

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $data = $options['data'];
        $refClass = new \ReflectionClass( $data );
        $classProperties = $refClass->getProperties( \ReflectionProperty::IS_PUBLIC );
        foreach ( $classProperties as $property ) {
            $name = $property->getName();
            /** @var \ReflectionNamedType|null $type */
            $type = $property->getType();
            if ( null === $type ) {
                return;
            }
            if ( 'requirements' === $name ) {
                $builder->add( 'requirements', ChoiceType::class, [
                    'multiple' => true,
                ] );
            }

            if ( $name === 'roles' ) {
                $builder->add( $name, ChoiceMultipleType::class, [
                    'label' => 'Rôles',
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] );
                continue;
            }

            if ( array_key_exists( $name, self::NAMES ) ) {
                $builder->add( $name, self::NAMES[$name], [
                    'required' => false,
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input-md',
                    ],
                ] );
            } elseif ( $type->getName() === \DateTimeInterface::class ) {
                $builder->add( $name, DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input-md flatpickr-date-input',
                    ],
                ] );
            } elseif ( array_key_exists( $type->getName(), self::TYPES ) ) {

                $builder->add( $name, self::TYPES[$type->getName()], [
                    'required' => !$type->allowsNull() && 'bool' !== $type->getName(),
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input-md',
                    ],
                ] );
            } else {
                throw new \RuntimeException( sprintf( 'Impossible de trouver le champs associé au type %s dans %s::%s', $type->getName(), $data::class, $name ) );
            }
        }
    }
}