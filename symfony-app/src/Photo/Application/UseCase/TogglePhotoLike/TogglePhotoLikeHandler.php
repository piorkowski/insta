<?php

declare(strict_types=1);

namespace Photo\Application\UseCase\TogglePhotoLike;

use Photo\Domain\Exception\PhotoNotFoundException;
use Photo\Domain\Repository\PhotoReadRepositoryInterface;
use Photo\Domain\Repository\PhotoWriteRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\Application\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class TogglePhotoLikeHandler
{
    public function __construct(
        private PhotoReadRepositoryInterface $photoReadRepository,
        private PhotoWriteRepositoryInterface $photoWriteRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(TogglePhotoLikeCommand $command): void
    {
        $photo = $this->photoReadRepository->findById($command->photoId);
        if (null === $photo) {
            throw new PhotoNotFoundException($command->photoId);
        }

        $userLikes = $this->photoReadRepository->findUserLikesForPhotos(
            $command->userId,
            [$command->photoId],
        );
        $isLiked = $userLikes[$command->photoId] ?? false;

        if ($isLiked) {
            $photo->setLikedByUserIds([$command->userId]);
            $photo->unlike($command->userId);
            $this->photoWriteRepository->removeLike($command->photoId, $command->userId);
            $this->logger->info('Photo unliked', ['photoId' => $command->photoId, 'userId' => $command->userId]);
        } else {
            $photo->like($command->userId);
            $this->photoWriteRepository->addLike($command->photoId, $command->userId);
            $this->logger->info('Photo liked', ['photoId' => $command->photoId, 'userId' => $command->userId]);
        }

        $this->photoWriteRepository->updateLikeCounter($command->photoId, $photo->getLikeCounter());
        $this->eventDispatcher->dispatch($photo);
    }
}
