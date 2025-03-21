<?php

namespace App\Http\Normalizer;

use App\Domain\Course\Entity\Course;
use App\Http\Encoder\PathEncoder;
use App\Normalizer\Normalizer;

class CoursePathNormalizer extends Normalizer
{
    public function normalize( mixed $object, ?string $format = null, array $context = [] ) : array
    {
        if ( $object instanceof Course) {
            return [
                'path' => 'app_course_show',
                'params' => ['slug' => $object->getSlug(), 'id' => $object->getId()]
            ];
        }
        throw new \RuntimeException("Can't normalize path");
    }

    public function supportsNormalization( mixed $data, ?string $format = null, array $context = [] ) : bool
    {
        return ($data instanceof Course) && PathEncoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Course::class => true,
        ];
    }
}