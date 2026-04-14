<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

abstract readonly class AbstractId implements ValueObjectInterface
{
    public function __construct(
        private int $value,
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException(\sprintf('%s must be a positive integer, got %d.', static::class, $value));
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof static && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
