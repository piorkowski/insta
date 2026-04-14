<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Application\EventHandler;

use Photo\Application\EventHandler\InvalidateCacheOnPhotoLiked;
use Photo\Domain\Event\PhotoLiked;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class InvalidateCacheOnPhotoLikedTest extends TestCase
{
    public function testInvalidatesCacheOnPhotoLiked(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects($this->once())
            ->method('invalidateTags')
            ->with(['photo_gallery']);

        $handler = new InvalidateCacheOnPhotoLiked($cache);
        $handler(new PhotoLiked(photoId: 1, userId: 1));
    }
}
