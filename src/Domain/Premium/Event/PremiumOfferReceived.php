<?php

namespace App\Domain\Premium\Event;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Premium\Entity\PremiumOffer;

class PremiumOfferReceived
{
    public function __construct( private readonly User $user, private readonly PremiumOffer $premiumOffer )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }

    public function getPremiumOffer() : PremiumOffer
    {
        return $this->premiumOffer;
    }
}