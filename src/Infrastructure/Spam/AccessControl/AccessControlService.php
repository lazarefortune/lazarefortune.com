<?php

namespace App\Infrastructure\Spam\AccessControl;

use App\Infrastructure\Spam\GeoLocationService;

class AccessControlService
{
    public function __construct(
        private readonly GeoLocationService $geoLocationService,
    ){}

    public function isAccessAllowed(string $ipAddress): bool
    {
        $countryCode = $this->geoLocationService->getCountryCode($ipAddress);

        if (null === $countryCode) {
            return false;
        }

        $allowedCountry = ['FR'];

        if (in_array($countryCode, $allowedCountry, true)) {
            return true;
        }

        return false;
    }
}