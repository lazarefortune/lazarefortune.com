<?php

namespace App\DataFixtures;

use App\Domain\Auth\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct( private readonly UserPasswordHasherInterface $passwordHasher )
    {
    }

    public function load( ObjectManager $manager ) : void
    {
        $user = new User();
        $user->setFullname( 'Admin' );
        $user->setEmail( 'admin@gmail.com' );
        $user->setPhone( '0601020304' );
        $user->setDateOfBirthday( new \DateTime( '1990-01-01' ) );
        $user->setPassword( $this->passwordHasher->hashPassword( $user, 'admin' ) );
        $user->setRoles( ['ROLE_ADMIN'] );
        $user->setIsVerified( true );
        $user->setCgu( true );
        $user->setCreatedAt( new \DateTime() );
        $user->setUpdatedAt( new \DateTime() );
        $manager->persist( $user );
        $this->addReference( 'user-1', $user );

        $faker = Factory::create( 'fr_FR' );
        $faker->addProvider( new \Faker\Provider\Internet( $faker ) );
        for ( $i = 0; $i < 20; $i++ ) {
            $user = new User();
            $user->setFullname( $faker->name );
            $user->setEmail( $faker->unique()->email );
            $user->setPhone( $faker->phoneNumber );
            $user->setDateOfBirthday( $faker->dateTimeBetween( '-50 years', '-18 years' ) );
            $user->setPassword( $this->passwordHasher->hashPassword( $user, 'password' ) );
            $user->setRoles( ['ROLE_CLIENT'] );
            $user->setIsVerified( $faker->boolean( 80 ) );
            $user->setCgu( true );
            $user->setCreatedAt( $faker->dateTimeBetween( '-1 year', 'now' ) );
            $user->setUpdatedAt( $faker->dateTimeBetween( $user->getCreatedAt(), 'now' ) );
            $manager->persist( $user );
            $this->addReference( 'user-' . ( $i + 2 ), $user );
        }

        $manager->flush();
    }
}