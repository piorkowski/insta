<?php

declare(strict_types=1);

namespace Photo\Domain\Aggregate;

use DateTimeImmutable;
use Photo\Domain\Event\PhotoLiked;
use Photo\Domain\Event\PhotoUnliked;
use Photo\Domain\Exception\PhotoAlreadyLikedException;
use Photo\Domain\Exception\PhotoNotLikedException;
use Photo\Domain\Exception\PhotoNotPersistedException;
use Photo\Domain\ValueObject\Camera;
use Photo\Domain\ValueObject\ImageUrl;
use Photo\Domain\ValueObject\Location;
use Photo\Domain\ValueObject\PhotoId;
use Shared\Domain\AggregateRoot;
use User\Domain\ValueObject\UserId;

class Photo extends AggregateRoot
{
    /** @var list<int> */
    private array $likedByUserIds = [];

    public function __construct(
        private ?PhotoId $id,
        private UserId $userId,
        private ImageUrl $imageUrl,
        private ?Location $location,
        private ?string $description,
        private ?Camera $camera,
        private ?DateTimeImmutable $takenAt,
        private int $likeCounter = 0,
    ) {
    }

    public static function create(
        UserId $userId,
        ImageUrl $imageUrl,
        ?Location $location = null,
        ?string $description = null,
        ?Camera $camera = null,
        ?DateTimeImmutable $takenAt = null,
    ): self {
        return new self(
            id: null,
            userId: $userId,
            imageUrl: $imageUrl,
            location: $location,
            description: $description,
            camera: $camera,
            takenAt: $takenAt,
        );
    }

    public function like(int $userId): void
    {
        if (null === $this->id) {
            throw new PhotoNotPersistedException();
        }

        if ($this->isLikedBy($userId)) {
            throw new PhotoAlreadyLikedException($this->id->value(), $userId);
        }

        $this->likedByUserIds[] = $userId;
        ++$this->likeCounter;
        $this->recordEvent(new PhotoLiked($this->id->value(), $userId));
    }

    public function unlike(int $userId): void
    {
        if (null === $this->id) {
            throw new PhotoNotPersistedException();
        }

        if (!$this->isLikedBy($userId)) {
            throw new PhotoNotLikedException($this->id->value(), $userId);
        }

        $this->likedByUserIds = array_values(
            array_filter($this->likedByUserIds, static fn (int $id) => $id !== $userId),
        );
        $this->likeCounter = max(0, $this->likeCounter - 1);
        $this->recordEvent(new PhotoUnliked($this->id->value(), $userId));
    }

    public function isLikedBy(int $userId): bool
    {
        return \in_array($userId, $this->likedByUserIds, true);
    }

    /** @param list<int> $userIds */
    public function setLikedByUserIds(array $userIds): void
    {
        $this->likedByUserIds = $userIds;
    }

    public function getId(): ?PhotoId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getImageUrl(): ImageUrl
    {
        return $this->imageUrl;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCamera(): ?Camera
    {
        return $this->camera;
    }

    public function getTakenAt(): ?DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function getLikeCounter(): int
    {
        return $this->likeCounter;
    }
}
