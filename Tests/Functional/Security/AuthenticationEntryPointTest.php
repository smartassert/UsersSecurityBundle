<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use SmartAssert\UsersSecurityBundle\Security\AuthenticationEntryPoint;

class AuthenticationEntryPointTest extends AbstractBaseFunctionalTest
{
    public function testServiceExistsInContainer(): void
    {
        $this->assertServiceExistsInContainer(AuthenticationEntryPoint::class);
    }
}
