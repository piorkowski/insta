<?php

declare(strict_types=1);

namespace User\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\ValueObjectInterface;

final readonly class Email implements ValueObjectInterface
{
    private string $email;

    public function __construct(string $email)
    {
        $trimmed = trim($email);
        if (!filter_var($trimmed, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(\sprintf('Invalid email address: "%s".', $email));
        }

        $this->email = $trimmed;
    }

    public function value(): string
    {
        return $this->email;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->email === $other->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
