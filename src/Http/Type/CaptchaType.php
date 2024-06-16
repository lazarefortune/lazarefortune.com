<?php

namespace App\Http\Type;

use App\Domain\AntiSpam\ChallengeInterface;
use App\Domain\AntiSpam\Puzzle\PuzzleChallenge;
use App\Validator\Challenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CaptchaType extends AbstractType
{
    public function __construct( private readonly ChallengeInterface $challenge, private readonly UrlGeneratorInterface $generator )
    {}

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'constraints' => [
                new NotBlank(),
                new Challenge()
            ],
            'route' => 'app_captcha'
        ] );
        parent::configureOptions( $resolver );
    }

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->add( 'challenge', HiddenType::class, [
            'attr' => [
                'class' => 'captcha-challenge',
            ],
        ] )
            ->add( 'answer', HiddenType::class, [
                'attr' => [
                    'class' => 'captcha-answer'
                ],
            ] );
        parent::buildForm( $builder, $options );
    }

    public function buildView( FormView $view, FormInterface $form, array $options ) : void
    {
        $key = $this->challenge->generateKey();
        $view->vars['attr'] = [
            'width' => PuzzleChallenge::WIDTH,
            'height' => PuzzleChallenge::HEIGHT,
            'pieceWidth' => PuzzleChallenge::PIECE_WIDTH,
            'pieceHeight' => PuzzleChallenge::PIECE_HEIGHT,
            'src' => $this->generator->generate( $options['route'], ['challenge' => $key], UrlGeneratorInterface::ABSOLUTE_URL ),
        ];
        $view->vars['challenge'] = $key;
        parent::buildView( $view, $form, $options );
    }
}