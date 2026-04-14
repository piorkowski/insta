<?php

declare(strict_types=1);

namespace Photo\Domain\Repository;

use Photo\Domain\Aggregate\Photo;

interface PhotoWriteRepositoryInterface
{
    public function save(Photo $photo): void;

    /** @param list<Photo> $photos */
    public function saveMany(array $photos): void;

    public function addLike(int $photoId, int $userId): void;

    public function removeLike(int $photoId, int $userId): void;

    public function updateLikeCounter(int $photoId, int $likeCounter): void;
}
