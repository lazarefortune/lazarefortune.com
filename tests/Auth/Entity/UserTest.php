<?php

declare(strict_types=1);

namespace App\Tests\Auth\Entity;

use App\Auth\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testUserHasRoleUserByDefault(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->assertContains(User::ROLE_USER, $user->getRoles());
    }

    public function testSetRolesPreservesAdminRoleAndAddsRoleUser(): void
    {
        $user = new User();
        $user->setRoles([User::ROLE_USER, User::ROLE_ADMIN]);

        $this->assertEqualsCanonicalizing(
            [User::ROLE_USER, User::ROLE_ADMIN],
            $user->getRoles(),
        );
    }
}
