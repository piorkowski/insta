<?php

declare(strict_types=1);

namespace Photo\Domain\Exception;

use DomainException;

final class PhotoNotPersistedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot perform this action on a photo that has not been persisted.');
    }
}
