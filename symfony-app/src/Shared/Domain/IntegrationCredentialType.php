<?php

declare(strict_types=1);

namespace Shared\Domain;

enum IntegrationCredentialType: string
{
    case API_TOKEN = 'api_token';
}
