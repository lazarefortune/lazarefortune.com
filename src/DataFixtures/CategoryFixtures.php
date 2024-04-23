<?php

namespace App\DataFixtures;

use App\Domain\Category\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public function load( ObjectManager $manager ) : void
    {
        $faker = Factory::create( 'fr_FR' );
        $faker->addProvider( new \Faker\Provider\Internet( $faker ) );

        $category = new Category();
        $category->setName( 'Coiffure' );
        $category->setDescription( 'Prestations de coiffure' );
        $category->setIsActive( true );
        $manager->persist( $category );

        // create 5 more categories
        for ( $i = 0; $i < 6; $i++ ) {
            $category = new Category();
            $category->setName( $faker->word );
            $category->setDescription( $faker->sentence );
            $category->setIsActive( $faker->boolean( 80 ) );
            $manager->persist( $category );
            $this->addReference( 'category-' . ( $i + 2 ), $category );
        }

        $manager->flush();

        $this->addReference( 'category-1', $category );
    }
}