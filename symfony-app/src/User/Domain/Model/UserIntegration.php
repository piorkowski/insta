<?php

declare(strict_types=1);

namespace User\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\IntegrationProvider;
use User\Domain\ValueObject\IntegrationCredentials;

class UserIntegration
{
    public function __construct(
        private ?int $id,
        private readonly int $userId,
        private readonly IntegrationProvider $provider,
        private IntegrationCredentials $credentials,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        int $userId,
        IntegrationProvider $provider,
        IntegrationCredentials $credentials,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            userId: $userId,
            provider: $provider,
            credentials: $credentials,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function updateCredentials(IntegrationCredentials $credentials): void
    {
        $this->credentials = $credentials;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getProvider(): IntegrationProvider
    {
        return $this->provider;
    }

    public function getCredentials(): IntegrationCredentials
    {
        return $this->credentials;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
