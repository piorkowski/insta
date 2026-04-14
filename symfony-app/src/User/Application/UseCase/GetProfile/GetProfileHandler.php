<?php

declare(strict_types=1);

namespace User\Application\UseCase\GetProfile;

use Photo\Domain\Repository\PhotoReadRepositoryInterface;
use Shared\Domain\IntegrationProvider;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use User\Application\DTO\UserDTO;
use User\Domain\Repository\UserReadRepositoryInterface;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetProfileHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
        private PhotoReadRepositoryInterface $photoReadRepository,
    ) {
    }

    public function __invoke(GetProfileQuery $query): ?UserDTO
    {
        $user = $this->userReadRepository->findById($query->userId);
        if (null === $user) {
            return null;
        }

        $userIdVo = $user->getId();
        if (null === $userIdVo) {
            return null;
        }

        $userId = $userIdVo->value();

        $photos = $this->photoReadRepository->findAllWithFilters(
            new \Photo\Domain\ValueObject\PhotoFilter(username: (string) $user->getUsername()),
        );

        $phoenixIntegration = $user->getIntegrationFor(IntegrationProvider::PHOENIX_API);

        return new UserDTO(
            id: $userId,
            username: (string) $user->getUsername(),
            email: (string) $user->getEmail(),
            name: $user->getName(),
            lastName: $user->getLastName(),
            age: $user->getAge(),
            bio: $user->getBio(),
            photoCount: \count($photos),
            phoenixApiToken: $phoenixIntegration?->getCredentials()->getValue(),
        );
    }
}
