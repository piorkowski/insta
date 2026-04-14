<?php

declare(strict_types=1);

namespace User\Infrastructure\Security;

use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use User\Domain\Repository\UserReadRepositoryInterface;

/** @implements UserProviderInterface<SecurityUser> */
final readonly class SecurityUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new InvalidArgumentException(\sprintf('Expected instance of %s, got %s.', SecurityUser::class, $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userReadRepository->findByUsername($identifier);
        if (null === $user) {
            $exception = new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
            $exception->setUserIdentifier($identifier);
            throw $exception;
        }

        return new SecurityUser($user);
    }
}
