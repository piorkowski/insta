<?php

declare(strict_types=1);

namespace Photo\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

final readonly class ImageUrl implements ValueObjectInterface
{
    public function __construct(private string $url)
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Image URL cannot be empty.');
        }

        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(\sprintf('Invalid image URL: "%s".', $url));
        }
    }

    public function value(): string
    {
        return $this->url;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->url === $other->url;
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
