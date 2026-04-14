<?php

declare(strict_types=1);

namespace Photo\UI\Action;

use DomainException;
use Photo\Application\UseCase\TogglePhotoLike\TogglePhotoLikeCommand;
use Shared\Application\Bus\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use User\Infrastructure\Security\SecurityUser;

#[Route('/photo/{id}/like', name: 'photo_like', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class LikePhotoAction extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('photo_like_'.$id, $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('home');
        }

        /** @var SecurityUser $securityUser */
        $securityUser = $this->getUser();

        try {
            $this->commandBus->dispatch(
                new TogglePhotoLikeCommand($id, $securityUser->getDomainUserId()),
            );
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('home');
    }
}
