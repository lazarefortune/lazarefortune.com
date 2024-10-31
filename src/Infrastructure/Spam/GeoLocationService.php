<?php

namespace App\Infrastructure\Spam;

use GeoIp2\Database\Reader;

class GeoLocationService
{
    private $reader;

    public function __construct(string $geoLiteDatabasePath)
    {
        $this->reader = new Reader($geoLiteDatabasePath);
    }

    public function getCountryCode(string $ip): ?string
    {
        try {
            $record = $this->reader->country($ip);
            return $record->country->isoCode;
        } catch (\Exception $e) {
            return null;
        }
    }
}