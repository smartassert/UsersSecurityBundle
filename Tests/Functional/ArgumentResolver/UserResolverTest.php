<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\ArgumentResolver;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\ArgumentResolver\UserResolver;
use SmartAssert\UsersSecurityBundle\Security\EmptyUser;
use SmartAssert\UsersSecurityBundle\Security\InvalidUser;
use SmartAssert\UsersSecurityBundle\Security\User;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserResolverTest extends TestCase
{
    private UserResolver $userResolver;
    private MockInterface $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $tokenStorage = \Mockery::mock(TokenStorageInterface::class);
        $this->tokenStorage = $tokenStorage;
        $container->set('security.token_storage', $this->tokenStorage);

        $userResolver = $container->get(UserResolver::class);
        \assert($userResolver instanceof UserResolver);
        $this->userResolver = $userResolver;
    }

    public function testResolveReturnsNoUser(): void
    {
        self::assertSame(
            [],
            $this->userResolver->resolve(new Request(), $this->createArgumentMetadata(self::class))
        );
    }

    #[DataProvider('resolveThrowsAccessDeniedExceptionDataProvider')]
    public function testResolveThrowsAccessDeniedException(?TokenInterface $securityToken): void
    {
        self::expectException(AccessDeniedException::class);

        $this->tokenStorage
            ->shouldReceive('getToken')
            ->andReturn($securityToken)
        ;

        $argumentMetadata = $this->createArgumentMetadata(User::class);

        $this->userResolver->resolve(new Request(), $argumentMetadata);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveThrowsAccessDeniedExceptionDataProvider(): array
    {
        return [
            'no security token' => [
                'securityToken' => null,
            ],
            'generic user' => [
                'securityToken' => (function () {
                    $token = \Mockery::mock(TokenInterface::class);
                    $token
                        ->shouldReceive('getUser')
                        ->andReturn(\Mockery::mock(UserInterface::class))
                    ;

                    return $token;
                })(),
            ],
            'empty user' => [
                'securityToken' => (function () {
                    $token = \Mockery::mock(TokenInterface::class);
                    $token
                        ->shouldReceive('getUser')
                        ->andReturn(new EmptyUser())
                    ;

                    return $token;
                })(),
            ],
            'invalid user' => [
                'securityToken' => (function () {
                    $token = \Mockery::mock(TokenInterface::class);
                    $token
                        ->shouldReceive('getUser')
                        ->andReturn(new InvalidUser('invalid'))
                    ;

                    return $token;
                })(),
            ],
        ];
    }

    public function testResolveSuccess(): void
    {
        $user = new User('valid', 'security token value');

        $securityToken = \Mockery::mock(TokenInterface::class);
        $securityToken
            ->shouldReceive('getUser')
            ->andReturn($user)
        ;

        $this->tokenStorage
            ->shouldReceive('getToken')
            ->andReturn($securityToken)
        ;

        $argumentMetadata = $this->createArgumentMetadata(User::class);

        $users = $this->userResolver->resolve(new Request(), $argumentMetadata);
        self::assertIsArray($users);
        self::assertCount(1, $users);
        self::assertSame($user, $users[0]);
    }

    private function createArgumentMetadata(string $type): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'argumentName',
            $type,
            false,
            false,
            null
        );
    }
}
