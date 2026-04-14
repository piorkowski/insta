<?php

declare(strict_types=1);

namespace Tests\InMemory;

use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Repository\PhotoWriteRepositoryInterface;

final class InMemoryPhotoWriteRepository implements PhotoWriteRepositoryInterface
{
    /** @var list<Photo> */
    public array $savedPhotos = [];

    /** @var list<array{photoId: int, userId: int}> */
    public array $addedLikes = [];

    /** @var list<array{photoId: int, userId: int}> */
    public array $removedLikes = [];

    /** @var array<int, int> photoId => likeCounter */
    public array $updatedCounters = [];

    public function save(Photo $photo): void
    {
        $this->savedPhotos[] = $photo;
    }

    /** @param list<Photo> $photos */
    public function saveMany(array $photos): void
    {
        foreach ($photos as $photo) {
            $this->savedPhotos[] = $photo;
        }
    }

    public function addLike(int $photoId, int $userId): void
    {
        $this->addedLikes[] = ['photoId' => $photoId, 'userId' => $userId];
    }

    public function removeLike(int $photoId, int $userId): void
    {
        $this->removedLikes[] = ['photoId' => $photoId, 'userId' => $userId];
    }

    public function updateLikeCounter(int $photoId, int $likeCounter): void
    {
        $this->updatedCounters[$photoId] = $likeCounter;
    }
}
