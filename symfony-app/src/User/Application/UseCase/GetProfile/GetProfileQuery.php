<?php

declare(strict_types=1);

namespace User\Application\UseCase\GetProfile;

final readonly class GetProfileQuery
{
    public function __construct(
        public int $userId,
    ) {
    }
}
