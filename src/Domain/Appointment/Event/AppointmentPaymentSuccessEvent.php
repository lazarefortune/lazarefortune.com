<?php

namespace App\Domain\Appointment\Event;

use Doctrine\Common\Collections\Collection;

class AppointmentPaymentSuccessEvent
{
    public function __construct(
        private readonly Collection $appointments,
    )
    {
    }

    public function getAppointments() : Collection
    {
        return $this->appointments;
    }
}