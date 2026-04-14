<?php

declare(strict_types=1);

namespace Shared\Domain;

enum IntegrationProvider: string
{
    case PHOENIX_API = 'phoenix_api';
}
