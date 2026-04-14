<?php

declare(strict_types=1);

namespace Photo\Application\EventHandler;

use Photo\Domain\Event\PhotoUnliked;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsMessageHandler]
final readonly class InvalidateCacheOnPhotoUnliked
{
    public function __construct(
        private TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(PhotoUnliked $event): void
    {
        $this->cache->invalidateTags(['photo_gallery']);
    }
}
