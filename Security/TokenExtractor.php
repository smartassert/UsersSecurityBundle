<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;

class TokenExtractor
{
    public function __construct(
        private readonly AuthorizationProperties $authorizationRequestProperties,
    ) {
    }

    public function extract(Request $request): ?string
    {
        $headers = $request->headers;
        $authorizationHeader = $headers->get($this->authorizationRequestProperties->headerName);
        if (null === $authorizationHeader) {
            return null;
        }

        if (false === str_starts_with($authorizationHeader, $this->authorizationRequestProperties->valuePrefix)) {
            return null;
        }

        return substr($authorizationHeader, strlen($this->authorizationRequestProperties->valuePrefix));
    }
}
