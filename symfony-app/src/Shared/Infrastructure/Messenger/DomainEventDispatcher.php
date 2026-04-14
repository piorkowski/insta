<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Messenger;

use Shared\Application\EventDispatcherInterface;
use Shared\Domain\AggregateRootInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DomainEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $eventBus,
    ) {
    }

    public function dispatch(AggregateRootInterface $aggregateRoot): void
    {
        foreach ($aggregateRoot->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
