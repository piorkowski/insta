<?php

declare(strict_types=1);

namespace Photo\Domain\Exception;

use DomainException;

final class PhotoAlreadyLikedException extends DomainException
{
    public function __construct(int $photoId, int $userId)
    {
        parent::__construct(\sprintf('User %d has already liked photo %d.', $userId, $photoId));
    }
}
