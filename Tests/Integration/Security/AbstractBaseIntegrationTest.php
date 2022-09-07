<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Integration\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBaseIntegrationTest extends TestCase
{
    protected const ADMIN_TOKEN = 'primary_admin_token';
    protected const USER_EMAIL = 'user@example.com';
    protected const USER_PASSWORD = 'password';

    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    /**
     * @param class-string $serviceClass
     */
    protected function assertServiceExistsInContainer(string $serviceClass): void
    {
        self::assertInstanceOf($serviceClass, $this->container->get($serviceClass));
    }
}
