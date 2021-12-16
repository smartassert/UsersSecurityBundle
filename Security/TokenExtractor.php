<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\UnicodeString;

class TokenExtractor
{
    public function __construct(
        private AuthorizationProperties $authorizationRequestProperties,
    ) {
    }

    public function extract(Request $request): ?string
    {
        $headers = $request->headers;
        $authorizationHeader = $headers->get($this->authorizationRequestProperties->getHeaderName());
        if (null === $authorizationHeader) {
            return null;
        }

        $valuePrefix = $this->authorizationRequestProperties->getValuePrefix();

        $authorizationHeaderString = new UnicodeString($authorizationHeader);
        if (false === $authorizationHeaderString->startsWith($valuePrefix)) {
            return null;
        }

        return (string) $authorizationHeaderString->trimPrefix($valuePrefix);
    }
}
