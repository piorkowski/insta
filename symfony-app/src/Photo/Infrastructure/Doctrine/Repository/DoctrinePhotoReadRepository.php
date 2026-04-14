<?php

declare(strict_types=1);

namespace Photo\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Repository\PhotoReadRepositoryInterface;
use Photo\Domain\ValueObject\Camera;
use Photo\Domain\ValueObject\ImageUrl;
use Photo\Domain\ValueObject\Location;
use Photo\Domain\ValueObject\PhotoFilter;
use Photo\Domain\ValueObject\PhotoId;
use Photo\Infrastructure\Doctrine\Entity\LikeEntity;
use Photo\Infrastructure\Doctrine\Entity\PhotoEntity;
use User\Domain\ValueObject\UserId;
use User\Infrastructure\Doctrine\Entity\UserEntity;

final readonly class DoctrinePhotoReadRepository implements PhotoReadRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /** @return list<Photo> */
    public function findAllWithFilters(?PhotoFilter $filter = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from(PhotoEntity::class, 'p')
            ->orderBy('p.id', 'ASC');

        if (null !== $filter) {
            if (null !== $filter->location) {
                $qb->andWhere('LOWER(p.location) LIKE LOWER(:location)')
                    ->setParameter('location', '%'.$filter->location.'%');
            }
            if (null !== $filter->camera) {
                $qb->andWhere('LOWER(p.camera) LIKE LOWER(:camera)')
                    ->setParameter('camera', '%'.$filter->camera.'%');
            }
            if (null !== $filter->description) {
                $qb->andWhere('LOWER(p.description) LIKE LOWER(:description)')
                    ->setParameter('description', '%'.$filter->description.'%');
            }
            if (null !== $filter->takenAtFrom) {
                $qb->andWhere('p.takenAt >= :takenAtFrom')
                    ->setParameter('takenAtFrom', $filter->takenAtFrom);
            }
            if (null !== $filter->takenAtTo) {
                $qb->andWhere('p.takenAt <= :takenAtTo')
                    ->setParameter('takenAtTo', $filter->takenAtTo);
            }
            if (null !== $filter->username) {
                $qb->join(UserEntity::class, 'u', 'WITH', 'u.id = p.userId')
                    ->andWhere('LOWER(u.username) LIKE LOWER(:username)')
                    ->setParameter('username', '%'.$filter->username.'%');
            }
        }

        /** @var list<PhotoEntity> $entities */
        $entities = $qb->getQuery()->getResult();

        return array_map(fn (PhotoEntity $entity): Photo => $this->toDomain($entity), $entities);
    }

    public function findById(int $id): ?Photo
    {
        $entity = $this->em->find(PhotoEntity::class, $id);
        if (!$entity instanceof PhotoEntity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /**
     * @param array<int, int> $photoIds
     *
     * @return array<int, bool>
     */
    public function findUserLikesForPhotos(int $userId, array $photoIds): array
    {
        if (empty($photoIds)) {
            return [];
        }

        /** @var list<array{photoId: int}> $result */
        $result = $this->em->createQueryBuilder()
            ->select('l.photoId')
            ->from(LikeEntity::class, 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.photoId IN (:photoIds)')
            ->setParameter('userId', $userId)
            ->setParameter('photoIds', $photoIds)
            ->getQuery()
            ->getArrayResult();

        /** @var list<int> $likedPhotoIds */
        $likedPhotoIds = array_column($result, 'photoId');
        /** @var array<int, bool> $likes */
        $likes = [];
        foreach ($photoIds as $photoId) {
            $likes[$photoId] = \in_array($photoId, $likedPhotoIds, false);
        }

        return $likes;
    }

    private function toDomain(PhotoEntity $entity): Photo
    {
        $entityId = $entity->getId();

        return new Photo(
            id: null !== $entityId ? new PhotoId($entityId) : null,
            userId: new UserId($entity->getUserId()),
            imageUrl: new ImageUrl($entity->getImageUrl()),
            location: $entity->getLocation() ? new Location($entity->getLocation()) : null,
            description: $entity->getDescription(),
            camera: $entity->getCamera() ? new Camera($entity->getCamera()) : null,
            takenAt: $entity->getTakenAt(),
            likeCounter: $entity->getLikeCounter(),
        );
    }
}
