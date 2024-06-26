<?php

namespace App\Domain\Attachment\Type;

use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Attachment\Validator\AttachmentExist;
use App\Domain\Attachment\Validator\NonExistingAttachment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AttachmentType extends TextType implements DataTransformerInterface
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly UploaderHelper $uploaderHelper, private readonly string $adminPrefix)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this);
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $form->getData()) {
            $view->vars['attr']['preview'] = $this->uploaderHelper->asset($form->getData());
        }
        $view->vars['attr']['overwrite'] = true;
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'attr' => [
                'is' => 'input-attachment',
                'data-endpoint' => '/'.trim($this->adminPrefix, '/').'/attachment',
//                'data-endpoint' => '/api',
            ],
            'constraints' => [
                new AttachmentExist(),
            ],
        ]);
        parent::configureOptions($resolver);
    }

    /**
     * @param ?Attachment $attachment
     */
    public function transform($attachment): ?int
    {
        if ($attachment instanceof Attachment) {
            return $attachment->getId();
        }

        return null;
    }

    /**
     * @param int $value
     */
    public function reverseTransform($value): ?Attachment
    {
        if (empty($value)) {
            return null;
        }

        return $this->em->getRepository(Attachment::class)->find($value) ?: new NonExistingAttachment($value);
    }
}