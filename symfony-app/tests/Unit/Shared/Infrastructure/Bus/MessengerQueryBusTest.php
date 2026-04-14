<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Bus;

use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Bus\MessengerQueryBus;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerQueryBusTest extends TestCase
{
    public function testReturnsHandlerResult(): void
    {
        $query = new stdClass();
        $expectedResult = ['some' => 'data'];

        $envelope = new Envelope($query, [new HandledStamp($expectedResult, 'handler')]);

        $innerBus = $this->createMock(MessageBusInterface::class);
        $innerBus->expects($this->once())
            ->method('dispatch')
            ->with($query)
            ->willReturn($envelope);

        $bus = new MessengerQueryBus($innerBus);
        $result = $bus->ask($query);

        $this->assertSame($expectedResult, $result);
    }

    public function testReturnsNullWhenNoHandlerStamp(): void
    {
        $query = new stdClass();
        $envelope = new Envelope($query);

        $innerBus = $this->createMock(MessageBusInterface::class);
        $innerBus->method('dispatch')->willReturn($envelope);

        $bus = new MessengerQueryBus($innerBus);
        $result = $bus->ask($query);

        $this->assertNull($result);
    }
}
