<?php

declare(strict_types=1);

namespace User\Domain\Exception;

use DomainException;

final class UserNotPersistedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot perform this action on a user that has not been persisted.');
    }
}
