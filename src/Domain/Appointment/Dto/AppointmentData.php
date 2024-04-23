<?php

namespace App\Domain\Appointment\Dto;

use App\Domain\Prestation\Entity\Prestation;
use App\Domain\Auth\Entity\User;
use DateTime;

class AppointmentData
{
    public function __construct(
        public User       $client,
        public Prestation $prestation,
        public DateTime   $date,
        public ?bool      $autoConfirm = false,
        public ?string    $slotId = null
    )
    {
    }
}
