<?php

declare(strict_types=1);

namespace User\UI\Action;

use DomainException;
use Shared\Application\Bus\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use User\Application\DTO\SaveIntegrationDTO;
use User\Application\UseCase\SaveIntegration\SaveIntegrationCommand;
use User\Infrastructure\Security\SecurityUser;
use ValueError;

#[Route('/profile/integration', name: 'save_integration', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class SaveIntegrationAction extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('save_integration', $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('profile');
        }

        /** @var SecurityUser $securityUser */
        $securityUser = $this->getUser();

        $dto = new SaveIntegrationDTO(
            provider: $request->request->getString('provider'),
            token: trim($request->request->getString('token')),
        );

        if ('' === $dto->provider || null === $dto->token || '' === trim($dto->token)) {
            $this->addFlash('error', 'Please select a provider and provide a valid token.');

            return $this->redirectToRoute('profile');
        }

        try {
            $this->commandBus->dispatch(new SaveIntegrationCommand(
                userId: $securityUser->getDomainUserId(),
                provider: $dto->getProvider(),
                token: $dto->token,
            ));
            $this->addFlash('success', 'Integration token saved successfully!');
        } catch (DomainException|ValueError $e) {
            $this->addFlash('error', 'Failed to save token: '.$e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
