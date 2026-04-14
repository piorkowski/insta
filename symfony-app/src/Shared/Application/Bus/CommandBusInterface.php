<?php

declare(strict_types=1);

namespace Shared\Application\Bus;

interface CommandBusInterface
{
    public function dispatch(object $command): void;
}
