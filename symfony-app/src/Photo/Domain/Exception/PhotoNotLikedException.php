<?php

declare(strict_types=1);

namespace Photo\Domain\Exception;

use DomainException;

final class PhotoNotLikedException extends DomainException
{
    public function __construct(int $photoId, int $userId)
    {
        parent::__construct(\sprintf('User %d has not liked photo %d.', $userId, $photoId));
    }
}
