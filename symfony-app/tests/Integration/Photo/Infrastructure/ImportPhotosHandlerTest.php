<?php

declare(strict_types=1);

namespace Tests\Integration\Photo\Infrastructure;

use Photo\Application\Exception\ImportFailedException;
use Photo\Application\UseCase\ImportPhotos\ImportPhotosCommand;
use Photo\Application\UseCase\ImportPhotos\ImportPhotosHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shared\Domain\IntegrationCredentialType;
use Shared\Domain\IntegrationProvider;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\InMemory\InMemoryPhotoWriteRepository;
use Tests\InMemory\InMemoryUserReadRepository;
use Tests\Stub\StubPhoenixApiClient;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\IntegrationCredentials;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class ImportPhotosHandlerTest extends TestCase
{
    private InMemoryUserReadRepository $userReadRepo;
    private InMemoryPhotoWriteRepository $photoWriteRepo;
    private ImportPhotosHandler $handler;

    protected function setUp(): void
    {
        $this->userReadRepo = new InMemoryUserReadRepository();
        $this->photoWriteRepo = new InMemoryPhotoWriteRepository();

        $eventBus = $this->createMock(MessageBusInterface::class);
        $eventBus->method('dispatch')->willReturnCallback(static fn ($message) => new Envelope($message));

        $this->handler = new ImportPhotosHandler(
            new StubPhoenixApiClient(),
            $this->photoWriteRepo,
            $this->userReadRepo,
            $eventBus,
            new NullLogger(),
        );
    }

    public function testImportsPhotosSuccessfully(): void
    {
        $user = new User(new UserId(1), new Username('testuser'), new Email('test@example.com'), 'Test', 'User', 30, null);
        $integration = UserIntegration::create(
            1,
            IntegrationProvider::PHOENIX_API,
            new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, StubPhoenixApiClient::VALID_TOKEN),
        );
        $user->setIntegrations([$integration]);
        $this->userReadRepo->addUser($user);

        ($this->handler)(new ImportPhotosCommand(1));

        $this->assertCount(2, $this->photoWriteRepo->savedPhotos);
    }

    public function testFailsWhenNoIntegrationConfigured(): void
    {
        $user = new User(new UserId(1), new Username('testuser'), new Email('test@example.com'), 'Test', 'User', 30, null);
        $this->userReadRepo->addUser($user);

        $this->expectException(ImportFailedException::class);
        $this->expectExceptionMessage('No Phoenix API integration configured');
        ($this->handler)(new ImportPhotosCommand(1));
    }

    public function testFailsWhenUserNotFound(): void
    {
        $this->expectException(ImportFailedException::class);
        $this->expectExceptionMessage('User not found');
        ($this->handler)(new ImportPhotosCommand(999));
    }
}
