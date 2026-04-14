<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Domain\ValueObject;

use InvalidArgumentException;
use Photo\Domain\ValueObject\Camera;
use PHPUnit\Framework\TestCase;

class CameraTest extends TestCase
{
    public function testValidCamera(): void
    {
        $camera = new Camera('Canon EOS R5');
        $this->assertSame('Canon EOS R5', $camera->name());
        $this->assertSame('Canon EOS R5', (string) $camera);
    }

    public function testTrimsWhitespace(): void
    {
        $camera = new Camera('  Sony A7 III  ');
        $this->assertSame('Sony A7 III', $camera->name());
    }

    public function testEmptyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Camera('');
    }

    public function testWhitespaceOnlyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Camera('   ');
    }

    public function testTooLongThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Camera(str_repeat('a', 256));
    }

    public function testEquality(): void
    {
        $a = new Camera('Canon EOS R5');
        $b = new Camera('Canon EOS R5');
        $c = new Camera('Nikon Z7 II');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
