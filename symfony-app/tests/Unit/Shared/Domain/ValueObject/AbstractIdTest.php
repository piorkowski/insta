<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\ValueObject;

use InvalidArgumentException;
use Photo\Domain\ValueObject\PhotoId;
use PHPUnit\Framework\TestCase;
use User\Domain\ValueObject\UserId;

class AbstractIdTest extends TestCase
{
    public function testValidPhotoId(): void
    {
        $id = new PhotoId(42);
        $this->assertSame(42, $id->value());
        $this->assertSame('42', (string) $id);
    }

    public function testValidUserId(): void
    {
        $id = new UserId(1);
        $this->assertSame(1, $id->value());
    }

    public function testZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhotoId(0);
    }

    public function testNegativeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UserId(-5);
    }

    public function testEqualitySameType(): void
    {
        $a = new PhotoId(1);
        $b = new PhotoId(1);
        $c = new PhotoId(2);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testEqualityDifferentTypesNotEqual(): void
    {
        $photoId = new PhotoId(1);
        $userId = new UserId(1);

        $this->assertFalse($photoId->equals($userId));
    }
}
