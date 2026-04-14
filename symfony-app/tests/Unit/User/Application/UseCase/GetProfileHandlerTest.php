<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\UseCase;

use Photo\Domain\Aggregate\Photo;
use Photo\Domain\ValueObject\ImageUrl;
use Photo\Domain\ValueObject\PhotoId;
use PHPUnit\Framework\TestCase;
use Shared\Domain\IntegrationCredentialType;
use Shared\Domain\IntegrationProvider;
use Tests\InMemory\InMemoryPhotoReadRepository;
use Tests\InMemory\InMemoryUserReadRepository;
use User\Application\UseCase\GetProfile\GetProfileHandler;
use User\Application\UseCase\GetProfile\GetProfileQuery;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\IntegrationCredentials;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class GetProfileHandlerTest extends TestCase
{
    private InMemoryUserReadRepository $userReadRepo;
    private InMemoryPhotoReadRepository $photoReadRepo;
    private GetProfileHandler $handler;

    protected function setUp(): void
    {
        $this->userReadRepo = new InMemoryUserReadRepository();
        $this->photoReadRepo = new InMemoryPhotoReadRepository();
        $this->handler = new GetProfileHandler($this->userReadRepo, $this->photoReadRepo);
    }

    public function testReturnsProfileWithPhotoCountAndIntegration(): void
    {
        $user = new User(new UserId(1), new Username('nature_lover'), new Email('nature@example.com'), 'Emma', 'Wilson', 28, 'Bio');
        $integration = UserIntegration::create(
            1,
            IntegrationProvider::PHOENIX_API,
            new IntegrationCredentials(IntegrationCredentialType::API_TOKEN, 'my-token'),
        );
        $user->setIntegrations([$integration]);
        $this->userReadRepo->addUser($user);

        $photo = new Photo(new PhotoId(1), new UserId(1), new ImageUrl('https://example.com/p1.jpg'), null, null, null, null);
        $this->photoReadRepo->addPhoto($photo);

        $result = ($this->handler)(new GetProfileQuery(1));

        $this->assertNotNull($result);
        $this->assertSame('nature_lover', $result->username);
        $this->assertSame('nature@example.com', $result->email);
        $this->assertSame('my-token', $result->phoenixApiToken);
    }

    public function testReturnsNullWhenUserNotFound(): void
    {
        $result = ($this->handler)(new GetProfileQuery(999));

        $this->assertNull($result);
    }
}
