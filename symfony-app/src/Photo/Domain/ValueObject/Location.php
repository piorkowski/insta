<?php

declare(strict_types=1);

namespace Photo\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

final readonly class Location implements ValueObjectInterface
{
    private string $location;

    public function __construct(string $location)
    {
        $trimmed = trim($location);
        if ('' === $trimmed) {
            throw new InvalidArgumentException('Location cannot be empty.');
        }

        if (mb_strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Location cannot exceed 255 characters.');
        }

        $this->location = $trimmed;
    }

    public function value(): string
    {
        return $this->location;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->location === $other->location;
    }

    public function __toString(): string
    {
        return $this->location;
    }
}
