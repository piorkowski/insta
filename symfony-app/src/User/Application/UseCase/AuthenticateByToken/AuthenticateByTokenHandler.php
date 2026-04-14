<?php

declare(strict_types=1);

namespace User\Application\UseCase\AuthenticateByToken;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use User\Application\DTO\UserDTO;
use User\Domain\Repository\UserReadRepositoryInterface;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class AuthenticateByTokenHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
    ) {
    }

    public function __invoke(AuthenticateByTokenQuery $query): ?UserDTO
    {
        $user = $this->userReadRepository->findByAuthToken($query->token);
        if (null === $user) {
            return null;
        }

        if ((string) $user->getUsername() !== $query->username) {
            return null;
        }

        $userIdVo = $user->getId();
        if (null === $userIdVo) {
            return null;
        }

        return new UserDTO(
            id: $userIdVo->value(),
            username: (string) $user->getUsername(),
            email: (string) $user->getEmail(),
            name: $user->getName(),
            lastName: $user->getLastName(),
            age: $user->getAge(),
            bio: $user->getBio(),
        );
    }
}
