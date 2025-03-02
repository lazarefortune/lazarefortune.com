<?php
namespace App\Http\Admin\Form\Course;

use App\Domain\Attachment\Type\AttachmentType;
use App\Domain\Course\Entity\Course;
use App\Http\Admin\Form\Field\TechnologiesType;
use App\Http\Admin\Form\Field\TechnologyChoiceType;
use App\Http\Admin\Form\Field\UserChoiceType;
use App\Http\Type\EditorType;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vich\UploaderBundle\Handler\UploadHandler;

class CourseEditForm extends AbstractType
{
    public function __construct(
        private readonly ?UploadHandler $uploaderHandler = null
    ) {}

    public function buildForm( FormBuilderInterface $builder, array $options )
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-input',
                    'data-slug-title' => ''
                ]
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-input text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                    'data-slug-input' => '',
                    'readonly' => true,
                ]
            ])
            ->add('author', UserChoiceType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('youtubeId', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('duration', TextType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-input'
                ]
            ])
            ->add('enableSource', SwitchType::class, [
                'label' => 'Activer / Désactiver le code source',
                'mapped' => false,
                'data' => !empty($options['data']->getSource()),
            ])
            ->add('sourceFile', FileType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Date de publication',
                'widget' => 'single_text',
                'html5' => false,
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'flatpickr-datetime form-input',
                ],
                'help' => 'Si la date est future, la vidéo sera planifiée.',
            ])
            ->add('online', SwitchType::class)
            ->add('premium', SwitchType::class)
            ->add('deprecatedBy', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('content', EditorType::class)
            ->add('image', AttachmentType::class)
            ->add('youtubeThumbnail', AttachmentType::class)
            ->add('mainTechnologies', TechnologyChoiceType::class, [
                'required' => false
            ])
            ->add('secondaryTechnologies', TechnologyChoiceType::class, [
                'required' => false
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Course $course */
            $course = $event->getData();
            if (!$course) {
                return;
            }

            // Si la date est déjà passée, on désactive le champ
            if ($course->getPublishedAt() < new \DateTimeImmutable()) {
                $form = $event->getForm();
                $form->add('publishedAt', DateTimeType::class, [
                    'label' => 'Date de publication',
                    'widget' => 'single_text',
                    'html5' => false,
                    'disabled' => true,
                    'label_attr' => ['class' => 'label'],
                    'attr' => [
                        'class' => 'flatpickr-datetime form-input',
                        'readonly' => true,
                    ],
                    'help' => 'Non modifiable car la date est passée.',
                ]);
            }

            // Si pas de YoutubeID => on supprime le champ duration
            if (!$course->getYoutubeId()) {
                $form = $event->getForm();
                $form->remove('duration');
            }
        });


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Course $course */
            $course = $event->getData();
            $form = $event->getForm();

            $course->setUpdatedAt(new \DateTimeImmutable());

            $enableSource = $form->get('enableSource')->getData(); // bool

            // Si false => on supprime le fichier
            if (!$enableSource && $course->getSource()) {
                $this->uploaderHandler->remove($course, 'sourceFile');
            }

            if ($course->isPremium() &&  ($course->getPublishedAt() > new \DateTimeImmutable())) {
                $course->setPublishedAt( new \DateTimeImmutable() );
            }
        });
    }

}