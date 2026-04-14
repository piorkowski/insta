<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Domain\ValueObject;

use InvalidArgumentException;
use Photo\Domain\ValueObject\Location;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    public function testValidLocation(): void
    {
        $location = new Location('Swiss Alps');
        $this->assertSame('Swiss Alps', $location->value());
    }

    public function testTrimsWhitespace(): void
    {
        $location = new Location('  Swiss Alps  ');
        $this->assertSame('Swiss Alps', $location->value());
    }

    public function testEmptyLocationThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Location('');
    }

    public function testWhitespaceOnlyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Location('   ');
    }

    public function testTooLongThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot exceed 255 characters');
        new Location(str_repeat('a', 256));
    }

    public function testEquality(): void
    {
        $a = new Location('Swiss Alps');
        $b = new Location('Swiss Alps');
        $c = new Location('Rocky Mountains');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
