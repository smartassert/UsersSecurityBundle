<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\AuthenticationEntryPoint;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationEntryPointTest extends TestCase
{
    public function testStart(): void
    {
        $authenticationEntryPoint = new AuthenticationEntryPoint();
        $response = $authenticationEntryPoint->start(\Mockery::mock(Request::class));

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }
}
