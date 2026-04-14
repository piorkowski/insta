<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Application\UseCase;

use Photo\Application\UseCase\TogglePhotoLike\TogglePhotoLikeCommand;
use Photo\Application\UseCase\TogglePhotoLike\TogglePhotoLikeHandler;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Exception\PhotoNotFoundException;
use Photo\Domain\ValueObject\ImageUrl;
use Photo\Domain\ValueObject\PhotoId;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shared\Infrastructure\Messenger\DomainEventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\InMemory\InMemoryPhotoReadRepository;
use Tests\InMemory\InMemoryPhotoWriteRepository;
use User\Domain\ValueObject\UserId;

class TogglePhotoLikeHandlerTest extends TestCase
{
    private InMemoryPhotoReadRepository $readRepo;
    private InMemoryPhotoWriteRepository $writeRepo;
    private TogglePhotoLikeHandler $handler;

    protected function setUp(): void
    {
        $this->readRepo = new InMemoryPhotoReadRepository();
        $this->writeRepo = new InMemoryPhotoWriteRepository();

        $eventBus = $this->createMock(MessageBusInterface::class);
        $eventBus->method('dispatch')->willReturnCallback(static fn ($msg) => new Envelope($msg));

        $this->handler = new TogglePhotoLikeHandler(
            $this->readRepo,
            $this->writeRepo,
            new DomainEventDispatcher($eventBus),
            new NullLogger(),
        );
    }

    public function testLikesPhotoWhenNotYetLiked(): void
    {
        $photo = new Photo(new PhotoId(1), new UserId(1), new ImageUrl('https://example.com/photo.jpg'), null, null, null, null);
        $this->readRepo->addPhoto($photo);

        ($this->handler)(new TogglePhotoLikeCommand(photoId: 1, userId: 42));

        $this->assertCount(1, $this->writeRepo->addedLikes);
        $this->assertSame(42, $this->writeRepo->addedLikes[0]['userId']);
        $this->assertSame(1, $this->writeRepo->updatedCounters[1]);
    }

    public function testUnlikesPhotoWhenAlreadyLiked(): void
    {
        $photo = new Photo(new PhotoId(1), new UserId(1), new ImageUrl('https://example.com/photo.jpg'), null, null, null, null, likeCounter: 1);
        $this->readRepo->addPhoto($photo);
        $this->readRepo->addLike(42, 1);

        ($this->handler)(new TogglePhotoLikeCommand(photoId: 1, userId: 42));

        $this->assertCount(1, $this->writeRepo->removedLikes);
        $this->assertSame(0, $this->writeRepo->updatedCounters[1]);
    }

    public function testThrowsWhenPhotoNotFound(): void
    {
        $this->expectException(PhotoNotFoundException::class);
        ($this->handler)(new TogglePhotoLikeCommand(photoId: 999, userId: 1));
    }
}
