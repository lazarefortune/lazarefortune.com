<?php

namespace App\Helper;

interface OptionManagerInterface
{
    public function get(string $key, ?string $default = null): ?string;

    public function set(string $key, string $value): void;

    public function delete(string $key): void;

    public function all(?array $keys = null): array;
}