<?php

declare(strict_types=1);

namespace Photo\Application\Exception;

use RuntimeException;

final class InvalidImportTokenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The provided access token is invalid or expired.');
    }
}
