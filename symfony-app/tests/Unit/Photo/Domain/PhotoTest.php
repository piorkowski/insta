<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Domain;

use DateTimeImmutable;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Event\PhotoLiked;
use Photo\Domain\Event\PhotoUnliked;
use Photo\Domain\Exception\PhotoAlreadyLikedException;
use Photo\Domain\Exception\PhotoNotLikedException;
use Photo\Domain\Exception\PhotoNotPersistedException;
use Photo\Domain\ValueObject\Camera;
use Photo\Domain\ValueObject\ImageUrl;
use Photo\Domain\ValueObject\Location;
use Photo\Domain\ValueObject\PhotoId;
use PHPUnit\Framework\TestCase;
use User\Domain\ValueObject\UserId;

class PhotoTest extends TestCase
{
    public function testCreatePhoto(): void
    {
        $photo = Photo::create(
            userId: new UserId(1),
            imageUrl: new ImageUrl('https://example.com/photo.jpg'),
            location: new Location('Swiss Alps'),
            description: 'A beautiful mountain',
            camera: new Camera('Canon EOS R5'),
            takenAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->assertNull($photo->getId());
        $this->assertSame(1, $photo->getUserId()->value());
        $this->assertSame('https://example.com/photo.jpg', (string) $photo->getImageUrl());
        $this->assertSame('Swiss Alps', (string) $photo->getLocation());
        $this->assertSame('A beautiful mountain', $photo->getDescription());
        $this->assertSame('Canon EOS R5', (string) $photo->getCamera());
        $this->assertSame(0, $photo->getLikeCounter());
    }

    public function testLikePhotoEmitsDomainEvent(): void
    {
        $photo = $this->createPhotoWithId(1);

        $photo->like(42);

        $this->assertSame(1, $photo->getLikeCounter());
        $this->assertTrue($photo->isLikedBy(42));

        $events = $photo->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PhotoLiked::class, $events[0]);
        $this->assertSame(1, $events[0]->photoId);
        $this->assertSame(42, $events[0]->userId);
    }

    public function testUnlikePhotoEmitsDomainEvent(): void
    {
        $photo = new Photo(
            id: new PhotoId(1), userId: new UserId(1),
            imageUrl: new ImageUrl('https://example.com/photo.jpg'),
            location: null, description: null, camera: null, takenAt: null,
            likeCounter: 1,
        );
        $photo->setLikedByUserIds([42]);

        $photo->unlike(42);

        $this->assertSame(0, $photo->getLikeCounter());
        $this->assertFalse($photo->isLikedBy(42));

        $events = $photo->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PhotoUnliked::class, $events[0]);
    }

    public function testCannotLikeTwice(): void
    {
        $photo = $this->createPhotoWithId(1);
        $photo->like(42);

        $this->expectException(PhotoAlreadyLikedException::class);
        $photo->like(42);
    }

    public function testCannotUnlikeIfNotLiked(): void
    {
        $photo = $this->createPhotoWithId(1);

        $this->expectException(PhotoNotLikedException::class);
        $photo->unlike(42);
    }

    public function testCannotLikeUnpersistedPhoto(): void
    {
        $photo = Photo::create(
            userId: new UserId(1),
            imageUrl: new ImageUrl('https://example.com/photo.jpg'),
        );

        $this->expectException(PhotoNotPersistedException::class);
        $photo->like(42);
    }

    public function testMultipleUsersCanLike(): void
    {
        $photo = $this->createPhotoWithId(1);

        $photo->like(1);
        $photo->like(2);
        $photo->like(3);

        $this->assertSame(3, $photo->getLikeCounter());
        $this->assertTrue($photo->isLikedBy(1));
        $this->assertTrue($photo->isLikedBy(2));
        $this->assertTrue($photo->isLikedBy(3));
        $this->assertFalse($photo->isLikedBy(4));
    }

    public function testPullDomainEventsClearsEvents(): void
    {
        $photo = $this->createPhotoWithId(1);
        $photo->like(1);

        $events = $photo->pullDomainEvents();
        $this->assertCount(1, $events);

        $events = $photo->pullDomainEvents();
        $this->assertCount(0, $events);
    }

    private function createPhotoWithId(int $id): Photo
    {
        return new Photo(
            id: new PhotoId($id),
            userId: new UserId(1),
            imageUrl: new ImageUrl('https://example.com/photo.jpg'),
            location: null,
            description: null,
            camera: null,
            takenAt: null,
        );
    }
}
