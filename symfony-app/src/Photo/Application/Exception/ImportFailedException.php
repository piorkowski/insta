<?php

declare(strict_types=1);

namespace Photo\Application\Exception;

use RuntimeException;
use Throwable;

final class ImportFailedException extends RuntimeException
{
    public function __construct(string $reason, ?Throwable $previous = null)
    {
        parent::__construct(\sprintf('Photo import failed: %s', $reason), 0, $previous);
    }
}
