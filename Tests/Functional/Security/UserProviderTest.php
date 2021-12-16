<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use SmartAssert\UsersSecurityBundle\Security\UserProvider;

class UserProviderTest extends AbstractBaseFunctionalTest
{
    public function testServiceExistsInContainer(): void
    {
        $this->assertServiceExistsInContainer(UserProvider::class);
    }
}
