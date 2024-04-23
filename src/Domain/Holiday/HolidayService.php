<?php

namespace App\Domain\Holiday;

use App\Domain\Holiday\Entity\Holiday;
use App\Domain\Holiday\Repository\HolidayRepository;
use Exception;

class HolidayService
{
    public function __construct(
        private readonly HolidayRepository $holidayRepository,
    )
    {
    }

    /**
     * @return array<Holiday>
     */
    public function getAll() : array
    {
        return $this->holidayRepository->findAll();
    }

    /**
     * @throws Exception
     */
    public function addHoliday( Holiday $holiday ) : void
    {
        // check if holiday already exists (startDate and endDate is between existing holiday)
        $existingHoliday = $this->holidayRepository->findHolidayBetweenDates( $holiday->getStartDate(), $holiday->getEndDate() );

        if ( $existingHoliday ) {
            throw new Exception( 'Cette période de fermeture existe déjà' );
        }

        if ( $holiday->getStartDate() > $holiday->getEndDate() ) {
            throw new Exception( 'La date de début doit être avant la date de fin' );
        }

        $this->holidayRepository->save( $holiday, true );
    }

    /**
     * @throws Exception
     */
    public function updateHoliday( Holiday $holiday ) : void
    {
        // check if holiday already exists (startDate and endDate is between existing holiday)
        /** @var Holiday $existingHoliday */
        $existingHoliday = $this->holidayRepository->findHolidayBetweenDates( $holiday->getStartDate(), $holiday->getEndDate() );


        if ( $existingHoliday && $existingHoliday[0]->getId() !== $holiday->getId() ) {
            throw new Exception( 'Holiday already exists' );
        }

        if ( $holiday->getStartDate() > $holiday->getEndDate() ) {
            throw new Exception( 'Start date must be before end date' );
        }

        $this->holidayRepository->save( $holiday, true );
    }

    /**
     * @return array<string, mixed>
     */
    public function getHolidaysForApi() : array
    {
        $holidays = $this->holidayRepository->findAll();
        $json_holidays = [];
        foreach ( $holidays as $holiday ) {
            $json_holidays[] = array(
                'id' => $holiday->getId(),
                'startDate' => $holiday->getStartDate()->format( 'Y-m-d' ),
                'endDate' => $holiday->getEndDate()->format( 'Y-m-d' ),
            );
        }
        return $json_holidays;
    }

    public function delete( Holiday $holiday, bool $true ) : void
    {
        $this->holidayRepository->remove( $holiday, $true );
    }
}