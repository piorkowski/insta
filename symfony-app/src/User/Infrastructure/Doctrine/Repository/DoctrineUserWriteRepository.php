<?php

declare(strict_types=1);

namespace User\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Shared\Domain\IntegrationProvider;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;
use User\Domain\Repository\UserWriteRepositoryInterface;
use User\Infrastructure\Doctrine\Entity\UserEntity;
use User\Infrastructure\Doctrine\Entity\UserIntegrationEntity;

final readonly class DoctrineUserWriteRepository implements UserWriteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(User $user): void
    {
        $entity = new UserEntity();
        $entity->setUsername((string) $user->getUsername());
        $entity->setEmail((string) $user->getEmail());
        $entity->setName($user->getName());
        $entity->setLastName($user->getLastName());
        $entity->setAge($user->getAge());
        $entity->setBio($user->getBio());

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function saveIntegration(UserIntegration $integration): void
    {
        $existingEntity = $this->em->getRepository(UserIntegrationEntity::class)
            ->findOneBy([
                'userId' => $integration->getUserId(),
                'provider' => $integration->getProvider()->value,
            ]);

        $existing = $existingEntity instanceof UserIntegrationEntity ? $existingEntity : null;

        if (null !== $existing) {
            $existing->setCredentialType($integration->getCredentials()->getType()->value);
            $existing->setCredentialValue($integration->getCredentials()->getValue());
            $existing->setUpdatedAt($integration->getUpdatedAt());
            $this->em->flush();

            return;
        }

        $entity = new UserIntegrationEntity();
        $entity->setUserId($integration->getUserId());
        $entity->setProvider($integration->getProvider()->value);
        $entity->setCredentialType($integration->getCredentials()->getType()->value);
        $entity->setCredentialValue($integration->getCredentials()->getValue());
        $entity->setCreatedAt($integration->getCreatedAt());
        $entity->setUpdatedAt($integration->getUpdatedAt());

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function removeIntegration(int $userId, IntegrationProvider $provider): void
    {
        $this->em->createQueryBuilder()
            ->delete(UserIntegrationEntity::class, 'i')
            ->where('i.userId = :userId')
            ->andWhere('i.provider = :provider')
            ->setParameter('userId', $userId)
            ->setParameter('provider', $provider->value)
            ->getQuery()
            ->execute();
    }
}
