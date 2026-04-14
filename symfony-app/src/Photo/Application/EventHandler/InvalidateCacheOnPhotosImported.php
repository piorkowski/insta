<?php

declare(strict_types=1);

namespace Photo\Application\EventHandler;

use Photo\Domain\Event\PhotosImported;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsMessageHandler]
final readonly class InvalidateCacheOnPhotosImported
{
    public function __construct(
        private TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(PhotosImported $event): void
    {
        $this->cache->invalidateTags(['photo_gallery']);
    }
}
