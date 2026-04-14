<?php

declare(strict_types=1);

namespace Tests\InMemory;

use User\Domain\Aggregate\User;
use User\Domain\Repository\UserReadRepositoryInterface;

final class InMemoryUserReadRepository implements UserReadRepositoryInterface
{
    /** @var array<int, User> */
    private array $users = [];

    /** @var array<string, int> token => userId */
    private array $authTokens = [];

    public function addUser(User $user): void
    {
        $id = $user->getId();
        if (null !== $id) {
            $this->users[$id->value()] = $user;
        }
    }

    public function addAuthToken(string $token, int $userId): void
    {
        $this->authTokens[$token] = $userId;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ((string) $user->getUsername() === $username) {
                return $user;
            }
        }

        return null;
    }

    public function findByAuthToken(string $token): ?User
    {
        $userId = $this->authTokens[$token] ?? null;
        if (null === $userId) {
            return null;
        }

        return $this->findById($userId);
    }
}
