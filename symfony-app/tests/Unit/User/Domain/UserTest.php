<?php

declare(strict_types=1);

namespace Tests\Unit\User\Domain;

use PHPUnit\Framework\TestCase;
use Shared\Domain\IntegrationCredentialType;
use Shared\Domain\IntegrationProvider;
use User\Domain\Aggregate\User;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\IntegrationCredentials;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class UserTest extends TestCase
{
    public function testAddIntegration(): void
    {
        $user = $this->createUser();

        $credentials = new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, 'my-token');
        $integration = $user->addIntegration(IntegrationProvider::PHOENIX_API, $credentials);

        $this->assertSame(IntegrationProvider::PHOENIX_API, $integration->getProvider());
        $this->assertSame('my-token', $integration->getCredentials()->getValue());
        $this->assertCount(1, $user->getIntegrations());
    }

    public function testAddIntegrationUpdatesExisting(): void
    {
        $user = $this->createUser();

        $credentials1 = new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, 'token-1');
        $user->addIntegration(IntegrationProvider::PHOENIX_API, $credentials1);

        $credentials2 = new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, 'token-2');
        $integration = $user->addIntegration(IntegrationProvider::PHOENIX_API, $credentials2);

        $this->assertCount(1, $user->getIntegrations());
        $this->assertSame('token-2', $integration->getCredentials()->getValue());
    }

    public function testRemoveIntegration(): void
    {
        $user = $this->createUser();

        $credentials = new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, 'my-token');
        $user->addIntegration(IntegrationProvider::PHOENIX_API, $credentials);

        $user->removeIntegration(IntegrationProvider::PHOENIX_API);

        $this->assertCount(0, $user->getIntegrations());
        $this->assertNull($user->getIntegrationFor(IntegrationProvider::PHOENIX_API));
    }

    public function testGetIntegrationForReturnsNullWhenNotFound(): void
    {
        $user = $this->createUser();
        $this->assertNull($user->getIntegrationFor(IntegrationProvider::PHOENIX_API));
    }

    private function createUser(): User
    {
        return new User(
            id: new UserId(1),
            username: new Username('testuser'),
            email: new Email('test@example.com'),
            name: 'Test',
            lastName: 'User',
            age: 30,
            bio: 'A test user',
        );
    }
}
