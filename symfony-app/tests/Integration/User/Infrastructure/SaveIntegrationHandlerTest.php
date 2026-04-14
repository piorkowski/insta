<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructure;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shared\Domain\IntegrationProvider;
use Tests\InMemory\InMemoryUserReadRepository;
use Tests\InMemory\InMemoryUserWriteRepository;
use User\Application\UseCase\SaveIntegration\SaveIntegrationCommand;
use User\Application\UseCase\SaveIntegration\SaveIntegrationHandler;
use User\Domain\Aggregate\User;
use User\Domain\Exception\UserNotFoundException;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class SaveIntegrationHandlerTest extends TestCase
{
    private InMemoryUserReadRepository $userReadRepo;
    private InMemoryUserWriteRepository $userWriteRepo;
    private SaveIntegrationHandler $handler;

    protected function setUp(): void
    {
        $this->userReadRepo = new InMemoryUserReadRepository();
        $this->userWriteRepo = new InMemoryUserWriteRepository();
        $this->handler = new SaveIntegrationHandler($this->userReadRepo, $this->userWriteRepo, new NullLogger());
    }

    public function testSavesIntegrationSuccessfully(): void
    {
        $user = new User(new UserId(1), new Username('testuser'), new Email('test@example.com'), 'Test', 'User', 30, null);
        $this->userReadRepo->addUser($user);

        ($this->handler)(new SaveIntegrationCommand(
            userId: 1,
            provider: IntegrationProvider::PHOENIX_API,
            token: 'my-phoenix-token',
        ));

        $this->assertCount(1, $this->userWriteRepo->savedIntegrations);
        $this->assertSame('my-phoenix-token', $this->userWriteRepo->savedIntegrations[0]->getCredentials()->getValue());
    }

    public function testFailsWhenUserNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);
        ($this->handler)(new SaveIntegrationCommand(
            userId: 999,
            provider: IntegrationProvider::PHOENIX_API,
            token: 'some-token',
        ));
    }
}
