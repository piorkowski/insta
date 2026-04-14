<?php

declare(strict_types=1);

namespace Photo\UI\Action;

use Photo\Application\DTO\PhotoDTO;
use Photo\Application\DTO\PhotoFilterDTO;
use Photo\Application\UseCase\ListPhotos\ListPhotosQuery;
use Shared\Application\Bus\QueryBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use User\Infrastructure\Security\SecurityUser;

#[Route('/', name: 'home', methods: ['GET'])]
final class ListPhotosAction extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(
        #[MapQueryString] ?PhotoFilterDTO $filter = null,
    ): Response {
        $user = $this->getUser();
        $currentUserId = $user instanceof SecurityUser ? $user->getDomainUserId() : null;

        $filter ??= new PhotoFilterDTO();

        /** @var list<PhotoDTO> $photos */
        $photos = $this->queryBus->ask(
            new ListPhotosQuery(
                currentUserId: $currentUserId,
                filter: $filter->isEmpty() ? null : $filter,
            ),
        );

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUserId' => $currentUserId,
            'filters' => $filter,
        ]);
    }
}
