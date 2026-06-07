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

    public function testDesignSystemPageContainsExpectedTwigComponents(): void
    {
        $client = static::createClient();
        $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="design-system-page"]');
        $this->assertSelectorExists('[data-testid="twig-design-system"]');
        $this->assertSelectorTextContains('h1', 'Design system V3');
        $this->assertSelectorTextContains('[data-testid="twig-design-system"]', 'Primary');
        $this->assertSelectorTextContains('[data-testid="twig-design-system"]', 'Card Twig');
        $this->assertSelectorTextContains('[data-testid="twig-design-system"]', 'Aucune vidéo');
        $this->assertSelectorExists('[data-studio-page="design-system"]');
    }

    public function testDesignSystemPageDoesNotExposeSensitiveData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/design-system');

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('DATABASE_URL', $crawler->html());
        $this->assertStringNotContainsString('APP_SECRET', $crawler->html());
        $this->assertStringNotContainsString('@gmail.com', $crawler->html());
    }
}
