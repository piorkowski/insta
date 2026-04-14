<?php

declare(strict_types=1);

namespace User\Domain\Repository;

use Shared\Domain\IntegrationProvider;
use User\Domain\Aggregate\User;
use User\Domain\Model\UserIntegration;

interface UserWriteRepositoryInterface
{
    public function save(User $user): void;

    public function saveIntegration(UserIntegration $integration): void;

    public function removeIntegration(int $userId, IntegrationProvider $provider): void;
}
