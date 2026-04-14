<?php

declare(strict_types=1);

namespace Photo\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

final readonly class Camera implements ValueObjectInterface
{
    private string $name;

    public function __construct(string $name)
    {
        $trimmed = trim($name);
        if ('' === $trimmed) {
            throw new InvalidArgumentException('Camera name cannot be empty.');
        }

        if (mb_strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Camera name cannot exceed 255 characters.');
        }

        $this->name = $trimmed;
    }

    public function value(): string
    {
        return $this->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
