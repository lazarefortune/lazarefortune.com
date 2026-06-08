<?php

declare(strict_types=1);

namespace App\Tests\Shared;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DesignSystemAccessTest extends WebTestCase
{
    public function testAnonymousUserCanAccessDesignSystem(): void
    {
        $client = static::createClient();
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
    }

    public function testDesignSystemPageContainsExpectedSections(): void
    {
        $client = static::createClient();
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="design-system-page"]');
        $this->assertSelectorExists('[data-testid="design-system-tokens"]');
        $this->assertSelectorExists('[data-testid="design-system-typography"]');
        $this->assertSelectorExists('[data-testid="design-system-buttons"]');
        $this->assertSelectorExists('[data-testid="design-system-button-sizes"]');
        $this->assertSelectorExists('[data-testid="design-system-forms"]');
        $this->assertSelectorExists('[data-testid="design-system-cards"]');
        $this->assertSelectorExists('[data-testid="design-system-badges"]');
        $this->assertSelectorExists('[data-testid="design-system-badge-sizes"]');
        $this->assertSelectorExists('[data-testid="design-system-alerts"]');
        $this->assertSelectorExists('[data-testid="design-system-flash"]');
        $this->assertSelectorExists('[data-testid="design-system-toast"]');
        $this->assertSelectorExists('[data-testid="design-system-tables"]');
        $this->assertSelectorExists('[data-testid="design-system-breadcrumb"]');
        $this->assertSelectorExists('[data-testid="design-system-split-button"]');
        $this->assertSelectorExists('[data-testid="design-system-empty-states"]');
        $this->assertSelectorExists('[data-testid="design-system-studio-preview"]');
        $this->assertSelectorExists('[data-testid="design-system-studio-layout-preview"]');
        $this->assertSelectorExists('[data-testid="design-system-theme"]');
        $this->assertSelectorTextContains('h1', 'Design system V3');
    }

    public function testDesignSystemPageContainsThemeToggle(): void
    {
        $client = static::createClient();
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-theme-toggle]');
    }

    public function testHtmlHasThemeAttribute(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSame('light', $crawler->filter('html')->attr('data-theme'));
    }

    public function testDesignSystemPageDoesNotExposeSensitiveData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('DATABASE_URL', $crawler->html());
        $this->assertStringNotContainsString('APP_SECRET', $crawler->html());
    }
}
