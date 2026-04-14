<?php

declare(strict_types=1);

namespace Tests\Unit\User\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use User\Domain\ValueObject\Email;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('user@example.com');
        $this->assertSame('user@example.com', $email->value());
    }

    public function testTrimsWhitespace(): void
    {
        $email = new Email('  user@example.com  ');
        $this->assertSame('user@example.com', $email->value());
    }

    public function testInvalidEmailThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');
        new Email('not-an-email');
    }

    public function testEmptyEmailThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('');
    }

    public function testEquality(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('user@example.com');
        $c = new Email('other@example.com');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
