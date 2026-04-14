<?php

declare(strict_types=1);

namespace Tests\Unit\User\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use User\Domain\ValueObject\Username;

class UsernameTest extends TestCase
{
    public function testValidUsername(): void
    {
        $username = new Username('nature_lover');
        $this->assertSame('nature_lover', $username->value());
        $this->assertSame('nature_lover', (string) $username);
    }

    public function testTrimsWhitespace(): void
    {
        $username = new Username('  test_user  ');
        $this->assertSame('test_user', $username->value());
    }

    public function testEmptyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Username('');
    }

    public function testInvalidCharactersThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid characters');
        new Username('user@name');
    }

    public function testSpacesInUsernameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Username('user name');
    }

    public function testTooLongThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Username(str_repeat('a', 181));
    }

    public function testEquality(): void
    {
        $a = new Username('nature_lover');
        $b = new Username('nature_lover');
        $c = new Username('wildlife_pro');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
