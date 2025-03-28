<?php

namespace App\Http\Normalizer;

use App\Domain\Auth\Core\Entity\User;
use App\Http\Encoder\PathEncoder;
use App\Normalizer\Normalizer;

class UserPathNormalizer extends Normalizer
{
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        if ($object instanceof User) {
            return [
                'path' => 'app_account_profile',
            ];
        }
        throw new \RuntimeException("Can't normalize path");
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof User && PathEncoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => true,
        ];
    }
}