<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\ListPhotos;

use Photo\Application\DTO\PhotoFilterDTO;

final readonly class ListPhotosQuery
{
    public function __construct(
        public ?int $currentUserId = null,
        public ?PhotoFilterDTO $filter = null,
    ) {
    }
}
