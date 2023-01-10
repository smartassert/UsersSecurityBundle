<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\ArgumentResolver;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\ArgumentResolver\UserResolver;
use SmartAssert\UsersSecurityBundle\Security\EmptyUser;
use SmartAssert\UsersSecurityBundle\Security\InvalidUser;
use SmartAssert\UsersSecurityBundle\Security\User;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserResolverTest extends TestCase
{
    private UserResolver $userResolver;
    private MockInterface $security;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $security = \Mockery::mock(Security::class);
        $this->security = $security;
        $container->set('security.helper', $this->security);

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

    /**
     * @dataProvider resolveThrowsAccessDeniedExceptionDataProvider
     */
    public function testResolveThrowsAccessDeniedException(UserInterface $securityUser): void
    {
        self::expectException(AccessDeniedException::class);

        $this->security
            ->shouldReceive('getUser')
            ->andReturn($securityUser)
        ;

        $argumentMetadata = $this->createArgumentMetadata(User::class);

        $this->userResolver->resolve(new Request(), $argumentMetadata);
    }

    /**
     * @return array<mixed>
     */
    public function resolveThrowsAccessDeniedExceptionDataProvider(): array
    {
        return [
            'generic user' => [
                'securityUser' => \Mockery::mock(UserInterface::class),
            ],
            'empty user' => [
                'securityUser' => new EmptyUser(),
            ],
            'invalid user' => [
                'securityUser' => new InvalidUser('invalid'),
            ],
        ];
    }

    public function testResolveSuccess(): void
    {
        $user = new User('valid', 'security token value');

        $this->security
            ->shouldReceive('getUser')
            ->andReturn($user)
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
