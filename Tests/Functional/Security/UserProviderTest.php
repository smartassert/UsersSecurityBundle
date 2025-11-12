<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\InvalidUser;
use SmartAssert\UsersSecurityBundle\Security\User;
use SmartAssert\UsersSecurityBundle\Security\UserProvider;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends TestCase
{
    private ContainerInterface $container;
    private UserProvider $userProvider;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();

        $userProvider = $this->container->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $this->userProvider = $userProvider;

        $requestStack = $this->container->get('request_stack');
        \assert($requestStack instanceof RequestStack);
        $this->requestStack = $requestStack;
    }

    #[DataProvider('loadUserByIdentifierDataProvider')]
    public function testLoadUserByIdentifier(Request $request, string $identifier, UserInterface $expected): void
    {
        $this->requestStack->push($request);

        self::assertEquals($expected, $this->userProvider->loadUserByIdentifier($identifier));
    }

    /**
     * @return array<mixed>
     */
    public static function loadUserByIdentifierDataProvider(): array
    {
        return [
            'empty request' => [
                'request' => new Request(),
                'identifier' => 'user1@example.com',
                'expected' => new InvalidUser('user1@example.com'),
            ],
            'empty security token' => [
                'request' => (function () {
                    $request = new Request();
                    $request->headers->set('authorization', 'Bearer ');

                    return $request;
                })(),
                'identifier' => 'user2@example.com',
                'expected' => new InvalidUser('user2@example.com'),
            ],
            'non-empty security token' => [
                'request' => (function () {
                    $request = new Request();
                    $request->headers->set('authorization', 'Bearer security-token-value');

                    return $request;
                })(),
                'identifier' => 'user3@example.com',
                'expected' => new User('user3@example.com', 'security-token-value'),
            ],
        ];
    }
}
