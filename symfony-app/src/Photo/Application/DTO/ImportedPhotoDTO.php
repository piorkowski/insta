<?php

declare(strict_types=1);

namespace Photo\Application\DTO;

final readonly class ImportedPhotoDTO
{
    public function __construct(
        public int $externalId,
        public string $photoUrl,
    ) {
    }
}
