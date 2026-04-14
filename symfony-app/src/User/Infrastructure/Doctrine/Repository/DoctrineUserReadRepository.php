<?php

declare(strict_types=1);

namespace User\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Shared\Domain\IntegrationCredentialType;
use Shared\Domain\IntegrationProvider;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;
use User\Domain\Repository\UserReadRepositoryInterface;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\IntegrationCredentials;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;
use User\Infrastructure\Doctrine\Entity\AuthTokenEntity;
use User\Infrastructure\Doctrine\Entity\UserEntity;
use User\Infrastructure\Doctrine\Entity\UserIntegrationEntity;

final readonly class DoctrineUserReadRepository implements UserReadRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function findById(int $id): ?User
    {
        $entity = $this->em->find(UserEntity::class, $id);
        if (!$entity instanceof UserEntity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByUsername(string $username): ?User
    {
        $entity = $this->em->getRepository(UserEntity::class)
            ->findOneBy(['username' => $username]);

        if (!$entity instanceof UserEntity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByAuthToken(string $token): ?User
    {
        $tokenEntity = $this->em->getRepository(AuthTokenEntity::class)
            ->findOneBy(['token' => $token]);

        if (!$tokenEntity instanceof AuthTokenEntity) {
            return null;
        }

        return $this->findById($tokenEntity->getUserId());
    }

    private function toDomain(UserEntity $entity): User
    {
        $entityId = $entity->getId();

        $user = new User(
            id: null !== $entityId ? new UserId($entityId) : null,
            username: new Username($entity->getUsername()),
            email: new Email($entity->getEmail()),
            name: $entity->getName(),
            lastName: $entity->getLastName(),
            age: $entity->getAge(),
            bio: $entity->getBio(),
        );

        /** @var list<UserIntegrationEntity> $integrationEntities */
        $integrationEntities = $this->em->getRepository(UserIntegrationEntity::class)
            ->findBy(['userId' => $entity->getId()]);

        $integrations = array_map(static fn (UserIntegrationEntity $ie): UserIntegration => new UserIntegration(
            id: $ie->getId(),
            userId: $ie->getUserId(),
            provider: IntegrationProvider::from($ie->getProvider()),
            credentials: new IntegrationCredentials(
                IntegrationCredentialType::from($ie->getCredentialType()),
                $ie->getCredentialValue(),
            ),
            createdAt: $ie->getCreatedAt(),
            updatedAt: $ie->getUpdatedAt(),
        ), $integrationEntities);

        $user->setIntegrations($integrations);

        return $user;
    }
}
