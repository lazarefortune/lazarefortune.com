<?php

declare(strict_types=1);

namespace App\Tests\Auth\Security;

use App\Auth\Entity\User;

final class StudioAccessTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedToLoginWhenAccessingStudio(): void
    {
        $client = static::createClient();
        $client->request('GET', '/studio');

        $this->assertResponseRedirects('/login');
    }

    public function testRoleAdminCanAccessStudio(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio');

        $this->assertResponseIsSuccessful();
    }

    public function testRoleUserCannotAccessStudio(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('user@example.com', []);

        $client->loginUser($user);
        $client->request('GET', '/studio');

        $this->assertResponseStatusCodeSame(403);
    }
}
