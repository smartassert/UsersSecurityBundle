<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Unit\Security;

use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use SmartAssert\UsersSecurityBundle\Security\SymfonyRequestTokenExtractor;
use SmartAssert\UsersSecurityBundle\Security\User;
use SmartAssert\UsersSecurityBundle\Security\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends TestCase
{
    private const USER_ID = '01FPZGHAG65E0N9ARWG6Y1RH34';
    private const USER_SECURITY_TOKEN = 'non-empty security token';

    private UserProvider $userProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $currentRequest = new Request();
        $currentRequest->headers->set('Authorization', 'Bearer ' . self::USER_SECURITY_TOKEN);

        $requestStack = new RequestStack();
        $requestStack->push($currentRequest);

        $this->userProvider = new UserProvider(
            $requestStack,
            new SymfonyRequestTokenExtractor(new HttpFactory(), new TokenExtractor()),
        );
    }

    public function testRefreshUser(): void
    {
        $user = new User(self::USER_ID, self::USER_SECURITY_TOKEN);

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

        self::assertEquals(new User(self::USER_ID, self::USER_SECURITY_TOKEN), $user);
    }
}
