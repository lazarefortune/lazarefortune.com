<?php

namespace App\Tests\Domain\Profile;

use App\Domain\Auth\Entity\EmailVerification;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\EmailVerificationRepository;
use App\Domain\Profile\Event\PasswordChangeSuccessEvent;
use App\Domain\Profile\Service\ProfileService;
use App\Domain\Profile\Dto\ProfileUpdateData;
use App\Infrastructure\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ProfileServiceTest extends TestCase
{
    private $profileService;
    private $entityManagerMock;
    private $eventDispatcherMock;
    private $emailVerificationRepositoryMock;
    private $tokenGeneratorServiceMock;
    private $passwordHasherMock;

    protected function setUp() : void
    {
        $this->entityManagerMock = $this->createMock( EntityManagerInterface::class );
        $this->eventDispatcherMock = $this->createMock( EventDispatcherInterface::class );
        $this->emailVerificationRepositoryMock = $this->createMock( EmailVerificationRepository::class );
        $this->tokenGeneratorServiceMock = $this->createMock( TokenGeneratorService::class );
        $this->passwordHasherMock = $this->createMock( UserPasswordHasherInterface::class );

        $this->profileService = new ProfileService(
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            $this->emailVerificationRepositoryMock,
            $this->tokenGeneratorServiceMock,
            $this->passwordHasherMock
        );
    }

    public function testUpdateProfile() : void
    {
        $user = new User();
        $user->setFullname( 'John Doe' );
        $user->setEmail( 'johndoe@gmail.com' );
        $user->setPhone( '123456789' );
        $user->setDateOfBirthday( new \DateTimeImmutable() );
        $profileUpdateData = new ProfileUpdateData( $user );

        $this->entityManagerMock->expects( $this->once() )->method( 'flush' );

        $this->profileService->updateProfile( $profileUpdateData );

        $this->assertEquals( 'John Doe', $user->getFullname() );
    }

    public function testUpdateEmail() : void
    {
        $user = new User();
        $emailVerification = new EmailVerification();
        $emailVerification->setAuthor( $user );
        $emailVerification->setEmail( 'newemail@example.com' );

        $this->entityManagerMock->expects( $this->once() )->method( 'remove' )->with( $emailVerification );
        $this->entityManagerMock->expects( $this->once() )->method( 'flush' );

        $this->profileService->updateEmail( $emailVerification );

        $this->assertEquals( 'newemail@example.com', $user->getEmail() );
    }

    public function testGetLatestValidEmailVerification() : void
    {
        $user = new User();
        $expectedResult = new EmailVerification();
        $this->emailVerificationRepositoryMock->expects( $this->once() )
            ->method( 'findLatestValidEmailVerification' )
            ->with( $user )
            ->willReturn( $expectedResult );

        $result = $this->profileService->getLatestValidEmailVerification( $user );

        $this->assertSame( $expectedResult, $result );
    }

    public function testUpdatePassword() : void
    {
        $user = new User();
        $newPassword = 'newPassword123';

        $this->passwordHasherMock->expects( $this->once() )
            ->method( 'hashPassword' )
            ->with( $user, $newPassword )
            ->willReturn( 'hashedPassword' );

        $this->entityManagerMock->expects( $this->once() )->method( 'flush' );

        $this->eventDispatcherMock->expects( $this->once() )->method( 'dispatch' )->with( new PasswordChangeSuccessEvent( $user ) );

        $this->profileService->updatePassword( $user, $newPassword );

        $this->assertEquals( 'hashedPassword', $user->getPassword() );
    }

}
