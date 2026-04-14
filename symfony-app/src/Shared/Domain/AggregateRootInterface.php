<?php

declare(strict_types=1);

namespace Shared\Domain;

interface AggregateRootInterface
{
    /** @return list<DomainEventInterface> */
    public function pullDomainEvents(): array;
}
