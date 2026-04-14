<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\TogglePhotoLike;

final readonly class TogglePhotoLikeCommand
{
    public function __construct(
        public int $photoId,
        public int $userId,
    ) {
    }
}
