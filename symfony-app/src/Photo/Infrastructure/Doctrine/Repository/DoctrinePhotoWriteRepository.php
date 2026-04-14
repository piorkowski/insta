<?php

declare(strict_types=1);

namespace Photo\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Repository\PhotoWriteRepositoryInterface;
use Photo\Infrastructure\Doctrine\Entity\LikeEntity;
use Photo\Infrastructure\Doctrine\Entity\PhotoEntity;

final readonly class DoctrinePhotoWriteRepository implements PhotoWriteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(Photo $photo): void
    {
        $entity = $this->toEntity($photo);
        $this->em->persist($entity);
        $this->em->flush();
    }

    /** @param list<Photo> $photos */
    public function saveMany(array $photos): void
    {
        foreach ($photos as $photo) {
            $entity = $this->toEntity($photo);
            $this->em->persist($entity);
        }
        $this->em->flush();
    }

    public function addLike(int $photoId, int $userId): void
    {
        $like = new LikeEntity();
        $like->setPhotoId($photoId);
        $like->setUserId($userId);
        $this->em->persist($like);
        $this->em->flush();
    }

    public function removeLike(int $photoId, int $userId): void
    {
        $qb = $this->em->createQueryBuilder()
            ->delete(LikeEntity::class, 'l')
            ->where('l.photoId = :photoId')
            ->andWhere('l.userId = :userId')
            ->setParameter('photoId', $photoId)
            ->setParameter('userId', $userId);

        $qb->getQuery()->execute();
    }

    public function updateLikeCounter(int $photoId, int $likeCounter): void
    {
        $this->em->createQueryBuilder()
            ->update(PhotoEntity::class, 'p')
            ->set('p.likeCounter', ':counter')
            ->where('p.id = :id')
            ->setParameter('counter', $likeCounter)
            ->setParameter('id', $photoId)
            ->getQuery()
            ->execute();
    }

    private function toEntity(Photo $photo): PhotoEntity
    {
        $entity = new PhotoEntity();
        $photoId = $photo->getId();
        if (null !== $photoId) {
            $entity->setId($photoId->value());
        }
        $entity->setUserId($photo->getUserId()->value());
        $entity->setImageUrl((string) $photo->getImageUrl());
        $entity->setLocation($photo->getLocation() ? (string) $photo->getLocation() : null);
        $entity->setDescription($photo->getDescription());
        $entity->setCamera($photo->getCamera() ? (string) $photo->getCamera() : null);
        $entity->setTakenAt($photo->getTakenAt());
        $entity->setLikeCounter($photo->getLikeCounter());

        return $entity;
    }
}
