<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Psr\Http\Message\RequestFactoryInterface;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use Symfony\Component\HttpFoundation\Request;

readonly class SymfonyRequestTokenExtractor
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private TokenExtractor $tokenExtractor,
    ) {}

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
