<?php

declare(strict_types=1);

namespace Shared\Domain;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function occurredAt(): DateTimeImmutable;
}
