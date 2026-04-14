<?php

declare(strict_types=1);

namespace User\Domain\Aggregate;

use Shared\Domain\AggregateRoot;
use Shared\Domain\IntegrationProvider;
use User\Domain\Exception\UserNotPersistedException;
use User\Domain\Model\UserIntegration;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\IntegrationCredentials;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class User extends AggregateRoot
{
    /** @var list<UserIntegration> */
    private array $integrations = [];

    public function __construct(
        private ?UserId $id,
        private Username $username,
        private Email $email,
        private ?string $name,
        private ?string $lastName,
        private ?int $age,
        private ?string $bio,
    ) {
    }

    public function addIntegration(IntegrationProvider $provider, IntegrationCredentials $credentials): UserIntegration
    {
        $existing = $this->getIntegrationFor($provider);
        if (null !== $existing) {
            $existing->updateCredentials($credentials);

            return $existing;
        }

        if (null === $this->id) {
            throw new UserNotPersistedException();
        }

        $integration = UserIntegration::create($this->id->value(), $provider, $credentials);
        $this->integrations[] = $integration;

        return $integration;
    }

    public function removeIntegration(IntegrationProvider $provider): void
    {
        $providerValue = $provider->value;
        $this->integrations = array_values(
            /** @phpstan-ignore notIdentical.alwaysFalse */
            array_filter($this->integrations, static fn (UserIntegration $i): bool => $i->getProvider()->value !== $providerValue),
        );
    }

    public function getIntegrationFor(IntegrationProvider $provider): ?UserIntegration
    {
        foreach ($this->integrations as $integration) {
            if ($integration->getProvider() === $provider) {
                return $integration;
            }
        }

        return null;
    }

    /** @param list<UserIntegration> $integrations */
    public function setIntegrations(array $integrations): void
    {
        $this->integrations = $integrations;
    }

    /** @return list<UserIntegration> */
    public function getIntegrations(): array
    {
        return $this->integrations;
    }

    public function getId(): ?UserId
    {
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }
}
