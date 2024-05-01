<?php

namespace App\DataFixtures;

use App\Domain\Appointment\Entity\Appointment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load( ObjectManager $manager ) : void
    {
        $faker = Factory::create( 'fr_FR' );
        $faker->addProvider( new \Faker\Provider\Internet( $faker ) );

        $appointment = new Appointment();
        $appointment->setDate( new \DateTime( 'now' ) );
        $appointment->setStartTime( new \DateTime( '11:30:00' ) );
        $appointment->setEndTime( new \DateTime( '12:30:00' ) );
        $appointment->setIsConfirmed( true );
        $appointment->setClient( $this->getReference( 'user-2' ) );
        $appointment->setPrestation( $this->getReference( 'prestation-1' ) );
        $appointment->setAccessToken( $faker->uuid );
        $appointment->setIsPaid( false );
        $appointment->setSubTotal(20);
        $appointment->setTotal(20);
        $appointment->setNbAdults( 1 );

        $manager->persist( $appointment );

        // create 6 more appointments
        for ( $i = 0; $i < 6; $i++ ) {
            $appointment = new Appointment();
            $appointment->setDate( new \DateTime( $faker->dateTimeBetween( '-1 month', '+1 month' )->format( 'Y-m-d' ) ) );
            $appointment->setStartTime( new \DateTime( '11:30:00' ) );
            $appointment->setEndTime( new \DateTime( '12:30:00' ) );
            $appointment->setIsConfirmed( $faker->boolean( 80 ) );
            $appointment->setClient( $this->getReference( 'user-' . rand( 2, 5 ) ) );
            $appointment->setPrestation( $this->getReference( 'prestation-' . ( $i + 2 ) ) );
            $appointment->setAccessToken( $faker->uuid );
            $appointment->setIsPaid( false );
            $amount = $faker->randomFloat(2, 10, 100);
            $appointment->setSubTotal($amount);
            $appointment->setTotal($amount);
            $appointment->setNbAdults( 1 );

            $manager->persist( $appointment );
        }

        $manager->flush();
    }

    public function getDependencies() : array
    {
        return [
            UserFixtures::class,
            PrestationFixtures::class,
        ];
    }
}