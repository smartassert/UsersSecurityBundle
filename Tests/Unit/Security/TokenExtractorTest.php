<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\AuthorizationProperties;
use SmartAssert\UsersSecurityBundle\Security\TokenExtractor;
use Symfony\Component\HttpFoundation\Request;

class TokenExtractorTest extends TestCase
{
    /**
     * @dataProvider extractDataProvider
     */
    public function testExtract(TokenExtractor $tokenExtractor, Request $request, ?string $expected): void
    {
        self::assertSame($expected, $tokenExtractor->extract($request));
    }

    /**
     * @return array<mixed>
     */
    public function extractDataProvider(): array
    {
        $headerName = AuthorizationProperties::DEFAULT_HEADER_NAME;
        $valuePrefix = AuthorizationProperties::DEFAULT_VALUE_PREFIX;

        $defaultTokenExtractor = new TokenExtractor(new AuthorizationProperties($headerName, $valuePrefix));

        return [
            'authorization header not present' => [
                'tokenExtractor' => $defaultTokenExtractor,
                'request' => new Request(),
                'expected' => null,
            ],
            'authorization header not starts with prefix' => [
                'tokenExtractor' => $defaultTokenExtractor,
                'request' => $this->createRequest([
                    $headerName => 'jwt token without prefix'
                ]),
                'expected' => null,
            ],
            'authorization header starts with prefix' => [
                'tokenExtractor' => $defaultTokenExtractor,
                'request' => $this->createRequest([
                    $headerName => $valuePrefix . 'jwt token'
                ]),
                'expected' => 'jwt token',
            ],
            'authorization header (uppercase) starts with prefix' => [
                'tokenExtractor' => $defaultTokenExtractor,
                'request' => $this->createRequest([
                    strtoupper($headerName) => $valuePrefix . 'jwt token'
                ]),
                'expected' => 'jwt token',
            ],
            'authorization header (lowercase) starts with prefix' => [
                'tokenExtractor' => $defaultTokenExtractor,
                'request' => $this->createRequest([
                    strtolower($headerName) => $valuePrefix . 'jwt token'
                ]),
                'expected' => 'jwt token',
            ],
        ];
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(array $headers): Request
    {
        $request = new Request();

        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return $request;
    }
}
