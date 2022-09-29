<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\UnicodeString;

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

        $valuePrefix = $this->authorizationRequestProperties->valuePrefix;

        $authorizationHeaderString = new UnicodeString($authorizationHeader);
        if (false === $authorizationHeaderString->startsWith($valuePrefix)) {
            return null;
        }

        return (string) $authorizationHeaderString->trimPrefix($valuePrefix);
    }
}
