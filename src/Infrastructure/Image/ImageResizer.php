<?php

namespace App\Infrastructure\Image;

use League\Glide\Urls\UrlBuilderFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Renvoie une URL redimensionnée pour une image donnée.
 */
class ImageResizer
{
    public function __construct(
        /**
         * Clef permettant de signer les URLs pour le redimensionnement
         */
        private readonly string $signKey,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function resize(?string $url, ?int $width = null, ?int $height = null): string
    {
        if ( empty($url) ) {
            return '';
        }

        if (null === $width && null === $height) {
            $url = $this->urlGenerator->generate('app_image_jpg', ['path' => trim($url, '/')]);
        } else {
            $url = $this->urlGenerator->generate('app_image_resize', ['path' => trim($url, '/'), 'width' => $width, 'height' => $height]);
        }
        $urlBuilder = UrlBuilderFactory::create('/', $this->signKey);

        return $urlBuilder->getUrl($url);
    }
}