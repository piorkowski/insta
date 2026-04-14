<?php

declare(strict_types=1);

namespace Photo\Infrastructure\Integrations\PhoenixApi;

use Photo\Application\DTO\ImportedPhotoDTO;
use Photo\Application\Exception\ImportFailedException;
use Photo\Application\Exception\InvalidImportTokenException;
use Photo\Application\Port\PhotoImportClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class HttpPhoenixApiClient implements PhotoImportClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $phoenixBaseUrl,
        private LoggerInterface $logger,
    ) {
    }

    /** @return list<ImportedPhotoDTO> */
    public function fetchPhotos(string $accessToken): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->phoenixBaseUrl.'/api/photos', [
                'headers' => [
                    'access-token' => $accessToken,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if (401 === $statusCode) {
                $this->logger->warning('Phoenix API authentication failed: invalid token');
                throw new InvalidImportTokenException();
            }

            if (200 !== $statusCode) {
                $this->logger->error('Phoenix API returned unexpected status', ['statusCode' => $statusCode]);
                throw new ImportFailedException(\sprintf('Phoenix API returned status %d.', $statusCode));
            }

            /** @var array{photos?: list<array{id: int, photo_url: string}>} $data */
            $data = $response->toArray();
            $photos = $data['photos'] ?? [];

            return array_map(
                static fn (array $photo): ImportedPhotoDTO => new ImportedPhotoDTO(
                    externalId: $photo['id'],
                    photoUrl: $photo['photo_url'],
                ),
                $photos,
            );
        } catch (InvalidImportTokenException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('Phoenix API request failed', ['error' => $e->getMessage()]);
            throw new ImportFailedException($e->getMessage(), $e);
        }
    }
}
