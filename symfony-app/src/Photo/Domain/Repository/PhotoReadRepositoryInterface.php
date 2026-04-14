<?php

declare(strict_types=1);

namespace Photo\Domain\Repository;

use Photo\Domain\Aggregate\Photo;
use Photo\Domain\ValueObject\PhotoFilter;

interface PhotoReadRepositoryInterface
{
    /** @return list<Photo> */
    public function findAllWithFilters(?PhotoFilter $filter = null): array;

    public function findById(int $id): ?Photo;

    /**
     * @param array<int, int> $photoIds
     *
     * @return array<int, bool> Map of photoId => isLiked
     */
    public function findUserLikesForPhotos(int $userId, array $photoIds): array;
}
