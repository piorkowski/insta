<?php

declare(strict_types=1);

namespace Tests\Stub;

use Photo\Application\DTO\ImportedPhotoDTO;
use Photo\Application\Exception\InvalidImportTokenException;
use Photo\Application\Port\PhotoImportClientInterface;

final class StubPhoenixApiClient implements PhotoImportClientInterface
{
    public const VALID_TOKEN = 'valid_test_token';
    public const INVALID_TOKEN = 'invalid_test_token';

    public function fetchPhotos(string $accessToken): array
    {
        if (self::INVALID_TOKEN === $accessToken) {
            throw new InvalidImportTokenException();
        }

        return [
            new ImportedPhotoDTO(externalId: 1, photoUrl: 'https://example.com/photo1.jpg'),
            new ImportedPhotoDTO(externalId: 2, photoUrl: 'https://example.com/photo2.jpg'),
        ];
    }
}
