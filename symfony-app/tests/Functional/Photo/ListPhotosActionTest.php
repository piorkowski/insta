<?php

declare(strict_types=1);

namespace Tests\Functional\Photo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListPhotosActionTest extends WebTestCase
{
    public function testHomePageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testHomePageContainsFilterForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="location"]');
        $this->assertSelectorExists('input[name="camera"]');
        $this->assertSelectorExists('input[name="description"]');
        $this->assertSelectorExists('input[name="username"]');
        $this->assertSelectorExists('input[name="taken_at_from"]');
        $this->assertSelectorExists('input[name="taken_at_to"]');
    }

    public function testHomePageWithFiltersDoesNotCrash(): void
    {
        $client = static::createClient();
        $client->request('GET', '/', [
            'location' => 'Alps',
            'camera' => 'Canon',
            'username' => 'nature',
        ]);

        $this->assertResponseIsSuccessful();
    }
}
