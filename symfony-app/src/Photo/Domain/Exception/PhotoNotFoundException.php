<?php

declare(strict_types=1);

namespace Photo\Domain\Exception;

use DomainException;

final class PhotoNotFoundException extends DomainException
{
    public function __construct(int $photoId)
    {
        parent::__construct(\sprintf('Photo with ID %d not found.', $photoId));
    }
}
