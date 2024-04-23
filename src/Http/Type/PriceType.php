<?php

namespace App\Http\Type;

use App\Helper\CentToEuroTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

class PriceType extends AbstractType
{
    public function __construct(
        private readonly CentToEuroTransformer $centToEuroTransformer
    )
    {
    }

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->addModelTransformer( $this->centToEuroTransformer );
    }

    public function getParent() : string
    {
        return MoneyType::class;
    }
}