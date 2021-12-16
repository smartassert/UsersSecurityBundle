<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\UserProvider;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;

class UserProviderTest extends TestCase
{
    public function testServiceExistsInContainer(): void
    {
        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $userProvider = $container->get(UserProvider::class);
        self::assertInstanceOf(UserProvider::class, $userProvider);
    }
}
