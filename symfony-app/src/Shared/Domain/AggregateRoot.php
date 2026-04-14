<?php

declare(strict_types=1);

namespace Shared\Domain;

abstract class AggregateRoot implements AggregateRootInterface
{
    /** @var list<DomainEventInterface> */
    private array $domainEvents = [];

    protected function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /** @return list<DomainEventInterface> */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
