<?php

declare(strict_types=1);

namespace User\Infrastructure\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use User\Domain\Repository\UserReadRepositoryInterface;

final class SessionAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserReadRepositoryInterface $userReadRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->getSession()->has('user_id');
    }

    public function authenticate(Request $request): Passport
    {
        $userId = $request->getSession()->get('user_id');
        if (!is_numeric($userId)) {
            throw new CustomUserMessageAuthenticationException('Invalid session.');
        }

        $user = $this->userReadRepository->findById((int) $userId);
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException('User no longer exists.');
        }

        return new SelfValidatingPassport(
            new UserBadge((string) $user->getUsername()),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->warning('Authentication failed', [
            'reason' => $exception->getMessage(),
            'ip' => $request->getClientIp(),
        ]);

        $request->getSession()->clear();

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }
}
