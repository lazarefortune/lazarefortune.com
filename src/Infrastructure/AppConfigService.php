<?php

namespace App\Infrastructure;

class AppConfigService
{

    public function __construct(
        private readonly string $appName,
    )
    {
    }

    public function getAppName() : string
    {
        return $this->appName;
    }
}
