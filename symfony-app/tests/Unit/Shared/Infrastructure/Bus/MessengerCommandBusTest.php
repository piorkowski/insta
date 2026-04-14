<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Bus;

use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Bus\MessengerCommandBus;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerCommandBusTest extends TestCase
{
    public function testDispatchesThroughMessenger(): void
    {
        $command = new stdClass();

        $innerBus = $this->createMock(MessageBusInterface::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(new Envelope($command));

        $bus = new MessengerCommandBus($innerBus);
        $bus->dispatch($command);
    }
}
