<?php

declare(strict_types=1);

namespace Tests\InMemory;

use Shared\Domain\IntegrationProvider;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;
use User\Domain\Repository\UserWriteRepositoryInterface;

final class InMemoryUserWriteRepository implements UserWriteRepositoryInterface
{
    /** @var list<User> */
    public array $savedUsers = [];

    /** @var list<UserIntegration> */
    public array $savedIntegrations = [];

    public function save(User $user): void
    {
        $this->savedUsers[] = $user;
    }

    public function saveIntegration(UserIntegration $integration): void
    {
        $this->savedIntegrations[] = $integration;
    }

    public function removeIntegration(int $userId, IntegrationProvider $provider): void
    {
    }
}
