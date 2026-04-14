<?php

declare(strict_types=1);

namespace Photo\Domain\ValueObject;

use DateTimeImmutable;

final readonly class PhotoFilter
{
    public function __construct(
        public ?string $location = null,
        public ?string $camera = null,
        public ?string $description = null,
        public ?DateTimeImmutable $takenAtFrom = null,
        public ?DateTimeImmutable $takenAtTo = null,
        public ?string $username = null,
    ) {
    }
}
