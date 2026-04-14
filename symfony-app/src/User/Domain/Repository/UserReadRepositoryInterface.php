<?php

declare(strict_types=1);

namespace User\Domain\Repository;

use User\Domain\Aggregate\User;

interface UserReadRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByAuthToken(string $token): ?User;
}
