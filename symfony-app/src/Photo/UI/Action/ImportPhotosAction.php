<?php

declare(strict_types=1);

namespace Photo\UI\Action;

use Photo\Application\Exception\ImportFailedException;
use Photo\Application\Exception\InvalidImportTokenException;
use Photo\Application\UseCase\ImportPhotos\ImportPhotosCommand;
use Shared\Application\Bus\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use User\Infrastructure\Security\SecurityUser;

#[Route('/photos/import', name: 'photos_import', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class ImportPhotosAction extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('photos_import', $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('profile');
        }

        /** @var SecurityUser $securityUser */
        $securityUser = $this->getUser();

        try {
            $this->commandBus->dispatch(
                new ImportPhotosCommand($securityUser->getDomainUserId()),
            );
            $this->addFlash('success', 'Photos imported successfully from Phoenix API!');
        } catch (InvalidImportTokenException) {
            $this->addFlash('error', 'Invalid Phoenix API token. Please check your token in your profile settings.');
        } catch (ImportFailedException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
