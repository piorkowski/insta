<?php

declare(strict_types=1);

namespace User\Application\UseCase\SaveIntegration;

use Shared\Domain\IntegrationProvider;

final readonly class SaveIntegrationCommand
{
    public function __construct(
        public int $userId,
        public IntegrationProvider $provider,
        public string $token,
    ) {
    }
}
