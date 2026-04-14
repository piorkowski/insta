<?php

declare(strict_types=1);

namespace Photo\Application\DTO;

use DateTimeImmutable;

final readonly class PhotoDTO
{
    public function __construct(
        public int $id,
        public string $imageUrl,
        public ?string $location,
        public ?string $description,
        public ?string $camera,
        public ?DateTimeImmutable $takenAt,
        public int $likeCounter,
        public string $username,
        public ?string $userName,
        public ?string $userLastName,
        public bool $isLikedByCurrentUser = false,
    ) {
    }
}
