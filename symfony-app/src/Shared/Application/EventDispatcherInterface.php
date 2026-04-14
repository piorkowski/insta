<?php

declare(strict_types=1);

namespace Shared\Application;

use Shared\Domain\AggregateRootInterface;

interface EventDispatcherInterface
{
    public function dispatch(AggregateRootInterface $aggregateRoot): void;
}
