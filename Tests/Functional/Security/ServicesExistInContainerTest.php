<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Security\AuthenticationEntryPoint;
use SmartAssert\UsersSecurityBundle\Security\SymfonyRequestTokenExtractor;
use SmartAssert\UsersSecurityBundle\Security\UserProvider;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServicesExistInContainerTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    /**
     * @param class-string $id
     */
    #[DataProvider('serviceExistsInContainerDataProvider')]
    public function testServiceExistsInContainer(string $id): void
    {
        self::assertInstanceOf($id, $this->container->get($id));
    }

    /**
     * @return array<mixed>
     */
    public static function serviceExistsInContainerDataProvider(): array
    {
        return [
            AuthenticationEntryPoint::class => [
                'id' => AuthenticationEntryPoint::class,
            ],
            SymfonyRequestTokenExtractor::class => [
                'id' => SymfonyRequestTokenExtractor::class,
            ],
            UserProvider::class => [
                'id' => UserProvider::class,
            ],
        ];
    }
}
