<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileActionTest extends WebTestCase
{
    public function testProfileDeniedWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLogoutRedirectsToHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/');
    }
}
