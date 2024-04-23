<?php

namespace App\Infrastructure;

class AppConfigService
{

    public function __construct(
        private readonly string $appName,
        private readonly string $adminEmail,
    )
    {
    }

    public function getAppName() : string
    {
        return $this->appName;
    }

    public function getAdminEmail() : string
    {
        return $this->adminEmail;
    }
}
