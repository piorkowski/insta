<?php

declare(strict_types=1);

namespace Tests\Unit\Photo\Domain\ValueObject;

use InvalidArgumentException;
use Photo\Domain\ValueObject\ImageUrl;
use PHPUnit\Framework\TestCase;

class ImageUrlTest extends TestCase
{
    public function testValidUrl(): void
    {
        $url = new ImageUrl('https://example.com/photo.jpg');
        $this->assertSame('https://example.com/photo.jpg', $url->value());
        $this->assertSame('https://example.com/photo.jpg', (string) $url);
    }

    public function testEmptyUrlThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image URL cannot be empty.');
        new ImageUrl('');
    }

    public function testInvalidUrlThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid image URL');
        new ImageUrl('not-a-url');
    }

    public function testEquality(): void
    {
        $url1 = new ImageUrl('https://example.com/a.jpg');
        $url2 = new ImageUrl('https://example.com/a.jpg');
        $url3 = new ImageUrl('https://example.com/b.jpg');

        $this->assertTrue($url1->equals($url2));
        $this->assertFalse($url1->equals($url3));
    }
}
