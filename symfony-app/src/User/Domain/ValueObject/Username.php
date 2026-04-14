<?php

declare(strict_types=1);

namespace User\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

final readonly class Username implements ValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            throw new InvalidArgumentException('Username cannot be empty.');
        }

        if (mb_strlen($trimmed) > 180) {
            throw new InvalidArgumentException('Username cannot exceed 180 characters.');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $trimmed)) {
            throw new InvalidArgumentException(\sprintf('Username "%s" contains invalid characters. Only alphanumeric and underscores allowed.', $trimmed));
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
