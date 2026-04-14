<?php

declare(strict_types=1);

namespace User\Application\UseCase\AuthenticateByToken;

final readonly class AuthenticateByTokenQuery
{
    public function __construct(
        public string $username,
        public string $token,
    ) {
    }
}
