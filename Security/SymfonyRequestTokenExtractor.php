<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Psr\Http\Message\RequestFactoryInterface;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use Symfony\Component\HttpFoundation\Request;

class SymfonyRequestTokenExtractor
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly TokenExtractor $tokenExtractor,
    ) {
    }

    public function extract(Request $request): ?string
    {
        return $this->tokenExtractor->extract(
            $this->requestFactory
                ->createRequest('GET', '')
                ->withHeader(
                    $this->tokenExtractor->headerName,
                    (string) $request->headers->get($this->tokenExtractor->headerName)
                )
        );
    }
}
