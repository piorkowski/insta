<?php

declare(strict_types=1);

namespace User\Domain\ValueObject;

use InvalidArgumentException;
use Shared\Domain\IntegrationCredentialType;
use Shared\Domain\ValueObjectInterface;

final readonly class IntegrationCredentials implements ValueObjectInterface
{
    public function __construct(
        private IntegrationCredentialType $type,
        private string $value,
    ) {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Integration credentials value cannot be empty.');
        }
    }

    public function getType(): IntegrationCredentialType
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self
            && $this->type === $other->type
            && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return \sprintf('%s:%s', $this->type->value, '***');
    }
}
