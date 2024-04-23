<?php

namespace App\Domain\Appointment\Event;

use App\Domain\Appointment\Entity\Appointment;

class ConfirmedAppointmentEvent
{
    public function __construct( private readonly Appointment $appointment )
    {
    }

    public function getAppointment() : Appointment
    {
        return $this->appointment;
    }
}