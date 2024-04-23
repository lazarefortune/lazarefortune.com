<?php

namespace App\DataFixtures;

use App\Domain\Prestation\Entity\Prestation;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PrestationFixtures extends Fixture implements DependentFixtureInterface
{

    public function load( ObjectManager $manager ) : void
    {
        $faker = Factory::create( 'fr_FR' );
        $faker->addProvider( new \Faker\Provider\Internet( $faker ) );

        $prestation = new Prestation();
        $prestation->setName( 'Shampoing' );
        $prestation->setDescription( 'Shampoing simple' );
        $prestation->setPrice( 2000 );
        $prestation->setDuration( new DateTime( '00:30:00' ) );
        $prestation->setAvalaibleSpacePerPrestation( 1 );
        $prestation->setCategoryPrestation( $this->getReference( 'category-1' ) );
        $prestation->setStartTime( new DateTime( '08:00:00' ) );
        $prestation->setEndTime( new DateTime( '18:00:00' ) );

        $prestation->setIsActive( true );
        $manager->persist( $prestation );
        $this->addReference( 'prestation-1', $prestation );

        // create 6 more prestations
        for ( $i = 0; $i < 6; $i++ ) {
            $prestation = new Prestation();
            $prestation->setName( $faker->word );
            $prestation->setDescription( $faker->sentence );
            $prestation->setPrice( $faker->numberBetween( 1000, 5000 ) );
            $prestation->setDuration( new DateTime( '00:30:00' ) );
            $prestation->setAvalaibleSpacePerPrestation( $faker->numberBetween( 1, 5 ) );
            $prestation->setCategoryPrestation( $this->getReference( 'category-' . ( $i + 2 ) ) );
            $prestation->setStartTime( new DateTime( '08:00:00' ) );
            $prestation->setEndTime( new DateTime( '18:00:00' ) );

            $prestation->setIsActive( $faker->boolean( 80 ) );
            $manager->persist( $prestation );

            $this->addReference( 'prestation-' . ( $i + 2 ), $prestation );
        }

        $manager->flush();
    }

    public function getDependencies() : array
    {
        return [CategoryFixtures::class];
    }
}