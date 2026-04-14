<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\ListPhotos;

use Photo\Application\DTO\PhotoDTO;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Repository\PhotoReadRepositoryInterface;
use Photo\Domain\ValueObject\PhotoFilter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use User\Domain\Repository\UserReadRepositoryInterface;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListPhotosHandler
{
    public function __construct(
        private PhotoReadRepositoryInterface $photoReadRepository,
        private UserReadRepositoryInterface $userReadRepository,
    ) {
    }

    /** @return list<PhotoDTO> */
    public function __invoke(ListPhotosQuery $query): array
    {
        $domainFilter = null !== $query->filter ? new PhotoFilter(
            location: $query->filter->location,
            camera: $query->filter->camera,
            description: $query->filter->description,
            takenAtFrom: $query->filter->getTakenAtFromDate(),
            takenAtTo: $query->filter->getTakenAtToDate(),
            username: $query->filter->username,
        ) : null;

        $photos = $this->photoReadRepository->findAllWithFilters($domainFilter);

        /** @var array<int, bool> $userLikes */
        $userLikes = [];
        if (null !== $query->currentUserId) {
            /** @var list<int> $photoIds */
            $photoIds = array_map(
                static fn (Photo $photo): int => $photo->getId()?->value() ?? 0,
                $photos,
            );
            $userLikes = $this->photoReadRepository->findUserLikesForPhotos(
                $query->currentUserId,
                $photoIds,
            );
        }

        $result = [];
        foreach ($photos as $photo) {
            $photoId = $photo->getId()?->value() ?? 0;
            $user = $this->userReadRepository->findById($photo->getUserId()->value());
            $result[] = new PhotoDTO(
                id: $photoId,
                imageUrl: (string) $photo->getImageUrl(),
                location: null !== $photo->getLocation() ? (string) $photo->getLocation() : null,
                description: $photo->getDescription(),
                camera: null !== $photo->getCamera() ? (string) $photo->getCamera() : null,
                takenAt: $photo->getTakenAt(),
                likeCounter: $photo->getLikeCounter(),
                username: null !== $user ? (string) $user->getUsername() : 'unknown',
                userName: $user?->getName(),
                userLastName: $user?->getLastName(),
                isLikedByCurrentUser: $userLikes[$photoId] ?? false,
            );
        }

        return $result;
    }
}
