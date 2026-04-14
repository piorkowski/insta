<?php

declare(strict_types=1);

namespace Tests\InMemory;

use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Repository\PhotoReadRepositoryInterface;
use Photo\Domain\ValueObject\PhotoFilter;

final class InMemoryPhotoReadRepository implements PhotoReadRepositoryInterface
{
    /** @var array<int, Photo> */
    private array $photos = [];

    /** @var array<string, true> key: "userId:photoId" */
    private array $likes = [];

    public function addPhoto(Photo $photo): void
    {
        $id = $photo->getId();
        if (null !== $id) {
            $this->photos[$id->value()] = $photo;
        }
    }

    public function addLike(int $userId, int $photoId): void
    {
        $this->likes[$userId.':'.$photoId] = true;
    }

    /** @return list<Photo> */
    public function findAllWithFilters(?PhotoFilter $filter = null): array
    {
        $photos = array_values($this->photos);

        if (null === $filter) {
            return $photos;
        }

        return array_values(array_filter($photos, static function (Photo $photo) use ($filter): bool {
            if (null !== $filter->location && null !== $photo->getLocation()) {
                if (!str_contains(mb_strtolower((string) $photo->getLocation()), mb_strtolower($filter->location))) {
                    return false;
                }
            }
            if (null !== $filter->camera && null !== $photo->getCamera()) {
                if (!str_contains(mb_strtolower((string) $photo->getCamera()), mb_strtolower($filter->camera))) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function findById(int $id): ?Photo
    {
        return $this->photos[$id] ?? null;
    }

    /**
     * @param array<int, int> $photoIds
     *
     * @return array<int, bool>
     */
    public function findUserLikesForPhotos(int $userId, array $photoIds): array
    {
        $result = [];
        foreach ($photoIds as $photoId) {
            $result[$photoId] = isset($this->likes[$userId.':'.$photoId]);
        }

        return $result;
    }
}
