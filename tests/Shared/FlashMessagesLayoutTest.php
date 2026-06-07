<?php

declare(strict_types=1);

namespace App\Tests\Shared;

use App\Auth\Entity\User;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;

final class FlashMessagesLayoutTest extends AuthenticatedWebTestCase
{
    public function testPublicLoginLayoutUsesInlineFlashContainer(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="inline"]');
        $this->assertSelectorNotExists('[data-flash-messages][data-flash-mode="floating"]');
    }

    public function testStudioLayoutUsesFloatingFlashContainer(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('flash-studio-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="floating"]');
    }

    public function testDesignSystemFloatingPreviewDocumentsAutoDismissAttributes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="design-system-flash"] [data-flash-dismiss]');
        $this->assertSelectorExists('[data-testid="design-system-flash"] [data-flash-auto-dismiss="true"]');
        $this->assertSelectorExists('[data-testid="design-system-flash"] [data-flash-auto-dismiss="true"] [data-flash-progress]');
        $this->assertSelectorExists('[data-testid="design-system-flash"] [data-flash-type="danger"]:not([data-flash-auto-dismiss])');
        $this->assertSelectorExists('[data-testid="design-system-toast"] [data-toast-timer="true"]');
    }
}
