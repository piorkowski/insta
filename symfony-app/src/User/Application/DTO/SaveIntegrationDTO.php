<?php

declare(strict_types=1);

namespace User\Application\DTO;

use Shared\Domain\IntegrationProvider;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SaveIntegrationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Please select an integration provider.')]
        public ?string $provider = null,

        #[Assert\NotBlank(message: 'Please provide an access token.')]
        public ?string $token = null,
    ) {
    }

    public function getProvider(): IntegrationProvider
    {
        return IntegrationProvider::from($this->provider ?? '');
    }
}
