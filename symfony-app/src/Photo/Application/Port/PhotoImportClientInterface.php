<?php

declare(strict_types=1);

namespace Photo\Application\Port;

use Photo\Application\DTO\ImportedPhotoDTO;
use Photo\Application\Exception\ImportFailedException;
use Photo\Application\Exception\InvalidImportTokenException;

interface PhotoImportClientInterface
{
    /**
     * @return list<ImportedPhotoDTO>
     *
     * @throws InvalidImportTokenException
     * @throws ImportFailedException
     */
    public function fetchPhotos(string $accessToken): array;
}
