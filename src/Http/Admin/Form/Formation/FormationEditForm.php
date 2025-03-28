<?php

namespace App\Http\Admin\Form\Formation;

use App\Domain\Attachment\Type\AttachmentType;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Http\Admin\Form\ChaptersForm;
use App\Http\Admin\Form\Field\TechnologyChoiceType;
use App\Http\Admin\Form\Field\UserChoiceType;
use App\Http\Type\EditorType;
use App\Http\Type\SwitchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vich\UploaderBundle\Handler\UploadHandler;

class FormationEditForm extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?UploadHandler $uploaderHandler = null,
    ) {}

    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        /** @var Formation $course */
        $formation = $options['data'];

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
                'disabled' => true,
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
                'help' => 'Si la date est future, la playlist sera planifiée.',
            ])
            ->add('youtubePlaylist', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
            ->add('online', SwitchType::class)
            ->add('image', AttachmentType::class)
            ->add('mainTechnologies', TechnologyChoiceType::class, [
                'required' => false,
                'mapped'   => false,
                'data' => $this->entityManager->getRepository(Technology::class)->findBy([
                    'id' => array_map(fn($t) => $t->getId(), $formation->getMainTechnologies())
                ]),
            ])
            ->add('secondaryTechnologies', TechnologyChoiceType::class, [
                'required' => false,
                'mapped'   => false,
                'data' => $this->entityManager->getRepository(Technology::class)->findBy([
                    'id' => array_map(fn($t) => $t->getId(), $formation->getSecondaryTechnologies())
                ]),
            ])
            ->add('short', EditorType::class)
            ->add('content', EditorType::class)
            ->add('chapters', ChaptersForm::class)
            ->add('deprecatedBy', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Formation $formation */
            $formation = $event->getData();
            if (!$formation) {
                return;
            }

            $form = $event->getForm();

            // Si la date est déjà passée, on désactive le champ
            if ($formation->getPublishedAt() < new \DateTimeImmutable()) {
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
        });


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Formation $formation */
            $formation = $event->getData();
            $form = $event->getForm();

            $formation->setUpdatedAt(new \DateTimeImmutable());

            // traitement techno
            $mainTechnologies = $form->get('mainTechnologies')->getData(); // depuis le formulaire
            $secondaryTechnologies = $form->get('secondaryTechnologies')->getData(); // depuis le formulaire

            // on les marque comme secondaires ou principales explicitement
            foreach ($mainTechnologies as $technology) {
                $technology->setSecondary(false);
            }

            foreach ($secondaryTechnologies as $technology) {
                $technology->setSecondary(true);
            }

            // Sync des technologies
            $removedUsages = $formation->syncTechnologies(
                array_merge($mainTechnologies, $secondaryTechnologies)
            );

            // Supprime explicitement les TechnologyUsages retirés de la relation
            foreach ($removedUsages as $usage) {
                $this->entityManager->remove($usage);
            }

            $chapters = $form->get('chapters')->getData();

            foreach ($chapters as $chapter) {
                /** @var Course $course */
                foreach ($chapter->getModules() as $course) {
                    $course->setFormation($formation);
                    $this->entityManager->persist($course);
                }
            }
            $formation->setChapters($chapters);
        });
    }
}