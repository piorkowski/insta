<?php

declare(strict_types=1);

namespace User\Domain\Exception;

use DomainException;

final class UserNotFoundException extends DomainException
{
    public function __construct(int $userId)
    {
        parent::__construct(\sprintf('User with ID %d not found.', $userId));
    }
}
