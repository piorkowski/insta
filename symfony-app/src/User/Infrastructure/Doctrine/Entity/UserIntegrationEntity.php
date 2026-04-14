<?php

declare(strict_types=1);

namespace User\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_integrations')]
#[ORM\UniqueConstraint(name: 'unique_user_provider', columns: ['user_id', 'provider'])]
class UserIntegrationEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string', length: 50)]
    private string $provider;

    #[ORM\Column(name: 'credential_type', type: 'string', length: 50)]
    private string $credentialType;

    #[ORM\Column(name: 'credential_value', type: 'string', length: 255)]
    private string $credentialValue;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCredentialType(): string
    {
        return $this->credentialType;
    }

    public function setCredentialType(string $credentialType): self
    {
        $this->credentialType = $credentialType;

        return $this;
    }

    public function getCredentialValue(): string
    {
        return $this->credentialValue;
    }

    public function setCredentialValue(string $credentialValue): self
    {
        $this->credentialValue = $credentialValue;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
