<?php

declare(strict_types=1);

namespace App\Video\Service;

final class YoutubeVideoIdExtractor
{
    private const ID_PATTERN = '/^[a-zA-Z0-9_-]{11}$/';

    public function extract(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if ($this->isValidId($input)) {
            return $input;
        }

        if (filter_var($input, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $this->extractFromUrl($input);
    }

    private function extractFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if ($host === 'youtu.be') {
            $candidate = explode('/', $path)[0] ?? '';

            return $this->isValidId($candidate) ? $candidate : null;
        }

        if ($this->isYoutubeHost($host)) {
            if (str_starts_with($path, 'watch')) {
                parse_str($parts['query'] ?? '', $query);
                $candidate = $query['v'] ?? '';

                return $this->isValidId($candidate) ? $candidate : null;
            }

            if (str_starts_with($path, 'shorts/')) {
                $candidate = substr($path, strlen('shorts/'));

                return $this->isValidId($candidate) ? $candidate : null;
            }
        }

        return null;
    }

    private function isYoutubeHost(string $host): bool
    {
        return $host === 'youtube.com'
            || $host === 'www.youtube.com'
            || str_ends_with($host, '.youtube.com');
    }

    private function isValidId(string $candidate): bool
    {
        return preg_match(self::ID_PATTERN, $candidate) === 1;
    }
}
