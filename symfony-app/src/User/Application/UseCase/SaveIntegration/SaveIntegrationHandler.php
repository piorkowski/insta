<?php

declare(strict_types=1);

namespace User\Application\UseCase\SaveIntegration;

use Psr\Log\LoggerInterface;
use Shared\Domain\IntegrationCredentialType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use User\Domain\Exception\UserNotFoundException;
use User\Domain\Repository\UserReadRepositoryInterface;
use User\Domain\Repository\UserWriteRepositoryInterface;
use User\Domain\ValueObject\IntegrationCredentials;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class SaveIntegrationHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
        private UserWriteRepositoryInterface $userWriteRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SaveIntegrationCommand $command): void
    {
        $user = $this->userReadRepository->findById($command->userId);
        if (null === $user) {
            throw new UserNotFoundException($command->userId);
        }

        $credentials = new IntegrationCredentials(
            IntegrationCredentialType::API_TOKEN,
            $command->token,
        );

        $integration = $user->addIntegration($command->provider, $credentials);

        $this->userWriteRepository->saveIntegration($integration);

        $this->logger->info('Integration saved', ['userId' => $command->userId, 'provider' => $command->provider->value]);
    }
}
