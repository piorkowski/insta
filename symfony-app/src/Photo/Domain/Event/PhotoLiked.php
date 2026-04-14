<?php

declare(strict_types=1);

namespace Photo\Domain\Event;

use DateTimeImmutable;
use Shared\Domain\DomainEventInterface;

final readonly class PhotoLiked implements DomainEventInterface
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public int $photoId,
        public int $userId,
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
