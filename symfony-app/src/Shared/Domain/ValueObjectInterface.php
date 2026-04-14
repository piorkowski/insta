<?php

declare(strict_types=1);

namespace Shared\Domain;

interface ValueObjectInterface
{
    public function equals(self $other): bool;

    public function __toString(): string;
}
