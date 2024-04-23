<?php

namespace App\Http\Api;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerService
{

    public function __construct( private SerializerInterface $serializer )
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer( $normalizers, $encoders );
    }

    public function serialize( mixed $data, string $format = 'json' ) : string
    {
        return $this->serializer->serialize( $data, $format );
    }
}