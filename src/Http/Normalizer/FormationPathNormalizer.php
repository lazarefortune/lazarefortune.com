<?php

namespace App\Http\Normalizer;

use App\Domain\Course\Entity\Formation;
use App\Http\Encoder\PathEncoder;
use App\Normalizer\Normalizer;

class FormationPathNormalizer extends Normalizer
{

    public function normalize( mixed $object, ?string $format = null, array $context = [] ) : array
    {
        if ( $object instanceof Formation ) {
            return [
                'path' => 'app_formation_show',
                'params' => ['slug' => $object->getSlug()]
            ];
        }

        throw new \RuntimeException();
    }

    public function supportsNormalization( mixed $data, ?string $format = null, array $context = [] ) : bool
    {
        return ($data instanceof Formation) && PathEncoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Formation::class => true,
        ];
    }
}