<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBaseFunctionalTest extends TestCase
{
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
