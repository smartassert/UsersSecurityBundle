<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\User;
use SmartAssert\UsersSecurityBundle\Security\UserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends TestCase
{
    private const USER_ID = '01FPZGHAG65E0N9ARWG6Y1RH34';

    private UserProvider $userProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = new UserProvider();
    }

    public function testRefreshUser(): void
    {
        $user = new User(self::USER_ID);

        self::assertSame($user, $this->userProvider->refreshUser($user));
    }

    public function testSupportsClass(): void
    {
        self::assertTrue($this->userProvider->supportsClass(User::class));
        self::assertFalse($this->userProvider->supportsClass(UserInterface::class));
    }

    public function testLoadUserByIdentifier(): void
    {
        $user = $this->userProvider->loadUserByIdentifier(self::USER_ID);

        self::assertEquals(new User(self::USER_ID), $user);
    }
}
