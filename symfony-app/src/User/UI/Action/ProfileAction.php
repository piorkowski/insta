<?php

declare(strict_types=1);

namespace User\UI\Action;

use Shared\Application\Bus\QueryBusInterface;
use Shared\Domain\IntegrationProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use User\Application\DTO\UserDTO;
use User\Application\UseCase\GetProfile\GetProfileQuery;
use User\Infrastructure\Security\SecurityUser;

#[Route('/profile', name: 'profile', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ProfileAction extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): Response
    {
        /** @var SecurityUser $securityUser */
        $securityUser = $this->getUser();

        /** @var UserDTO|null $userDTO */
        $userDTO = $this->queryBus->ask(
            new GetProfileQuery($securityUser->getDomainUserId()),
        );

        if (null === $userDTO) {
            throw $this->createNotFoundException('User profile not found.');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $userDTO,
            'providers' => IntegrationProvider::cases(),
        ]);
    }
}
