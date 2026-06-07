<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;

final class StudioShellLayoutTest extends AuthenticatedWebTestCase
{
    public function testStudioShellContainsFixedSidebarAndScrollableMainMarkers(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-shell-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-studio-shell]');
        $this->assertSelectorExists('[data-studio-sidebar]');
        $this->assertSelectorExists('[data-studio-content]');
        $this->assertSelectorExists('[data-studio-topbar]');
    }

    public function testStudioShowsAccountAndLogoutLinksForConnectedAdmin(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-shell-account@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-studio-account-link]');
        $this->assertSelectorExists('[data-studio-logout-link]');
        $this->assertSelectorTextContains('[data-studio-user-menu]', 'studio-shell-account@example.com');
    }
}
