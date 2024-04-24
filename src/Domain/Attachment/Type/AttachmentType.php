<?php

namespace App\Domain\Attachment\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AttachmentType extends TextType implements DataTransformerInterface
{
}