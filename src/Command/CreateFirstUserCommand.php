<?php

namespace App\Command;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Core\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-first-user',
    description: 'Create the first user',
)]
class CreateFirstUserCommand extends Command
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $em;

    public function __construct( UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->em = $em;
        parent::__construct();
    }

    protected function configure() : void
    {
        $this
            ->setDescription( 'Create the first user' )
            ->setHelp( 'This command allows you to create the first user' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int
    {
        $io = new SymfonyStyle( $input, $output );
        $io->title( 'First user creation' );
        $io->text( 'This command allows you to create the first user' );

        // Count users
        $users = $this->userRepository->findAll();
        if ( count( $users ) > 0 ) {
            $io->error( 'There is already a user in the database' );
            return Command::FAILURE;
        }

        $io->section( 'User creation' );
        $io->text( 'Please enter the following information' );
        $io->text( 'Press CTRL+C to quit' );
        $io->newLine();

        $email = $io->ask( 'Email' );
        $password = $io->askHidden( 'Password' );
        $passwordConfirm = $io->askHidden( 'Confirm password' );

        if ( $password !== $passwordConfirm ) {
            $io->error( 'Passwords do not match' );
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy( ['email' => $email] );

        if ( $user ) {
            $io->error( 'User already exists' );
            return Command::FAILURE;
        }

        $fullName = $io->ask( 'What is your full name?' );
        $phone = $io->ask( 'What is your phone number?' );


        try {
            $newUser = new User();
            $newUser->setEmail( $email );
            $newUser->setPassword( $this->userPasswordHasher->hashPassword( $newUser, $password ) );
            $newUser->setFullName( $fullName );
            $newUser->setPhone( $phone );
            $newUser->setIsVerified( true );
            $newUser->setRoles( ['ROLE_SUPER_ADMIN'] );
            $newUser->setCgu( true );
            $this->em->persist( $newUser );
            $this->em->flush();

            $io->success( 'User created successfully' );
            return Command::SUCCESS;
        } catch ( \Exception $e ) {
            $io->error( $e->getMessage() );
            return Command::FAILURE;
        }


    }

}