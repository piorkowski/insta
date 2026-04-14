<?php

declare(strict_types=1);

namespace User\Infrastructure\Security;

use LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use User\Domain\Aggregate\User;

final readonly class SecurityUser implements UserInterface
{
    public function __construct(
        private User $domainUser,
    ) {
    }

    public function getDomainUser(): User
    {
        return $this->domainUser;
    }

    public function getDomainUserId(): int
    {
        $id = $this->domainUser->getId();
        if (null === $id) {
            throw new LogicException('SecurityUser must wrap a persisted User.');
        }

        return $id->value();
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->domainUser->getUsername();
    }
}
