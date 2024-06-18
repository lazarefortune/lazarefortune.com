<?php

namespace App\Http\Encoder;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class PathEncoder implements EncoderInterface
{
    final public const FORMAT = 'path';

    public function __construct( private readonly UrlGeneratorInterface $urlGenerator )
    {
    }

    public function encode( mixed $data, string $format, array $context = [] ) : string
    {
        ['path' => $path, 'params' => $params] = array_merge( ['params' => []], $data );

        $hash = isset( $data['hash'] ) ? '#' . $data['hash'] : '';

        $url = $context['url'] ?? false;

        return $this->urlGenerator->generate( $path, $params, $url ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH ) . $hash;
    }

    public function supportsEncoding( string $format ) : bool
    {
        return $format === self::FORMAT;
    }
}