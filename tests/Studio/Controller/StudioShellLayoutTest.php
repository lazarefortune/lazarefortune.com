<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;

final class StudioShellLayoutTest extends AuthenticatedWebTestCase
{
    public function testStudioShellContainsLegacyLayoutMarkers(): void
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
        $this->assertSelectorExists('[data-testid="studio-menu-toggle"]');
        $this->assertSelectorExists('[data-testid="studio-quit-link"]');
    }

    public function testStudioShowsHeaderUserMenuWithAccountAndLogoutLinks(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-shell-account@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-studio-header-user]');
        $this->assertSelectorExists('[data-testid="studio-header-user-toggle"]');
        $this->assertSelectorExists('[data-studio-account-link]');
        $this->assertSelectorExists('[data-studio-logout-link]');
        $this->assertSelectorTextContains('[data-studio-header-user]', 'studio-shell-account@example.com');
    }

    public function testDesignSystemContainsStudioLayoutSection(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-shell-design-system@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="design-system-studio-preview"]');
        $this->assertSelectorExists('[data-testid="design-system-studio-layout-preview"]');
        $this->assertSelectorTextContains('[data-testid="design-system-studio-preview"]', 'Studio layout');
    }
}
