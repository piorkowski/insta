<?php

declare(strict_types=1);

namespace User\UI\Action;

use Shared\Application\Bus\QueryBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use User\Application\DTO\UserDTO;
use User\Application\UseCase\AuthenticateByToken\AuthenticateByTokenQuery;

#[Route('/auth/{username}/{token}', name: 'auth_login', methods: ['GET'])]
final class LoginAction extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(string $username, string $token, Request $request): Response
    {
        /** @var UserDTO|null $userDTO */
        $userDTO = $this->queryBus->ask(
            new AuthenticateByTokenQuery($username, $token),
        );

        if (null === $userDTO) {
            $this->addFlash('error', 'Invalid authentication credentials.');

            return $this->redirectToRoute('home');
        }

        $session = $request->getSession();
        $session->set('user_id', $userDTO->id);
        $session->set('username', $userDTO->username);

        $this->addFlash('success', \sprintf('Welcome back, %s!', $userDTO->username));

        return $this->redirectToRoute('home');
    }
}
