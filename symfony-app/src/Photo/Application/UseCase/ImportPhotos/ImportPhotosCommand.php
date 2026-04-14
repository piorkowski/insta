<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\ImportPhotos;

final readonly class ImportPhotosCommand
{
    public function __construct(
        public int $userId,
    ) {
    }
}
