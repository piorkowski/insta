<?php

declare(strict_types=1);

namespace Photo\Application\DTO;

use DateTimeImmutable;
use Exception;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class PhotoFilterDTO
{
    public function __construct(
        public ?string $location = null,
        public ?string $camera = null,
        public ?string $description = null,
        #[SerializedName('taken_at_from')]
        public ?string $takenAtFrom = null,
        #[SerializedName('taken_at_to')]
        public ?string $takenAtTo = null,
        public ?string $username = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return null === $this->location
            && null === $this->camera
            && null === $this->description
            && null === $this->takenAtFrom
            && null === $this->takenAtTo
            && null === $this->username;
    }

    public function getTakenAtFromDate(): ?DateTimeImmutable
    {
        return $this->parseDate($this->takenAtFrom);
    }

    public function getTakenAtToDate(): ?DateTimeImmutable
    {
        return $this->parseDate($this->takenAtTo);
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }
}
