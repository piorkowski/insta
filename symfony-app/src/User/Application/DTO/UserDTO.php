<?php

declare(strict_types=1);

namespace User\Application\DTO;

final readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public ?string $name,
        public ?string $lastName,
        public ?int $age,
        public ?string $bio,
        public int $photoCount = 0,
        public ?string $phoenixApiToken = null,
    ) {
    }
}
