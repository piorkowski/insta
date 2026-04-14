<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\ImportPhotos;

use Photo\Application\Exception\ImportFailedException;
use Photo\Application\Port\PhotoImportClientInterface;
use Photo\Domain\Aggregate\Photo;
use Photo\Domain\Event\PhotosImported;
use Photo\Domain\Repository\PhotoWriteRepositoryInterface;
use Photo\Domain\ValueObject\ImageUrl;
use Psr\Log\LoggerInterface;
use Shared\Domain\IntegrationProvider;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use User\Domain\Repository\UserReadRepositoryInterface;
use User\Domain\ValueObject\UserId;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ImportPhotosHandler
{
    public function __construct(
        private PhotoImportClientInterface $importClient,
        private PhotoWriteRepositoryInterface $photoWriteRepository,
        private UserReadRepositoryInterface $userReadRepository,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImportPhotosCommand $command): void
    {
        $user = $this->userReadRepository->findById($command->userId);
        if (null === $user) {
            throw new ImportFailedException('User not found.');
        }

        $integration = $user->getIntegrationFor(IntegrationProvider::PHOENIX_API);
        if (null === $integration) {
            $this->logger->warning('Photo import attempted without integration configured', ['userId' => $command->userId]);
            throw new ImportFailedException('No Phoenix API integration configured. Please add your access token in your profile.');
        }

        $this->logger->info('Starting photo import from Phoenix API', ['userId' => $command->userId]);

        $importedPhotos = $this->importClient->fetchPhotos($integration->getCredentials()->getValue());

        $userIdVo = new UserId($command->userId);

        $photos = [];
        foreach ($importedPhotos as $imported) {
            $photos[] = Photo::create(
                userId: $userIdVo,
                imageUrl: new ImageUrl($imported->photoUrl),
            );
        }

        if ([] !== $photos) {
            $this->photoWriteRepository->saveMany($photos);
        }

        $this->eventBus->dispatch(new PhotosImported($command->userId, \count($photos)));

        $this->logger->info('Photo import completed', ['userId' => $command->userId, 'count' => \count($photos)]);
    }
}
