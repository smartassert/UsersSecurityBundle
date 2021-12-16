<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use SmartAssert\UsersSecurityBundle\Security\TokenExtractor;

class TokenExtractorTest extends AbstractBaseFunctionalTest
{
    public function testServiceExistsInContainer(): void
    {
        $this->assertServiceExistsInContainer(TokenExtractor::class);
    }
}
