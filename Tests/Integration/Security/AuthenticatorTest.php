<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Integration\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use SmartAssert\UsersSecurityBundle\Security\Authenticator;
use SmartAssert\UsersSecurityBundle\Tests\Functional\TestingKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticatorTest extends TestCase
{
    private const USER_EMAIL = 'user@example.com';

    protected ContainerInterface $container;
    private Authenticator $authenticator;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();

        $authenticator = $this->container->get(Authenticator::class);
        \assert($authenticator instanceof Authenticator);
        $this->authenticator = $authenticator;
    }

    /**
     * @dataProvider authenticateFailureNoTokenInRequestDataProvider
     */
    public function testAuthenticateFailureNoTokenInRequest(Request $request): void
    {
        self::expectExceptionObject(
            new CustomUserMessageAuthenticationException('Invalid user token')
        );

        $this->authenticator->authenticate($request);
    }

    /**
     * @return array<mixed>
     */
    public function authenticateFailureNoTokenInRequestDataProvider(): array
    {
        return [
            'token missing' => [
                'request' => new Request(),
            ],
            'token empty' => [
                'request' => new Request(server: [
                    'HTTP_AUTHORIZATION' => 'Bearer '
                ]),
            ],
        ];
    }

    /**
     * @dataProvider authenticateFailureInvalidTokenDataProvider
     *
     * @param non-empty-string $userTokenValue
     */
    public function testAuthenticateFailureInvalidToken(string $userTokenValue, Request $request): void
    {
        self::expectExceptionObject(
            new CustomUserMessageAuthenticationException('Invalid user token')
        );

        $this->authenticator->authenticate($request);
    }

    /**
     * @return array<mixed>
     */
    public function authenticateFailureInvalidTokenDataProvider(): array
    {
        return [
            'invalid user token' => [
                'userTokenValue' => 'invalid-token',
                'request' => new Request(server: [
                    'HTTP_AUTHORIZATION' => 'Bearer invalid-token',
                ]),
            ],
        ];
    }

    public function testAuthenticateSuccess(): void
    {
        $apiTokenProvider = $this->container->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $apiToken = $apiTokenProvider->get(self::USER_EMAIL);

        $passport = $this->authenticator->authenticate(new Request(server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $apiToken
        ]));

        $userProvider = $this->container->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $frontendUser = $userProvider->get(self::USER_EMAIL);

        $expectedPassport = new SelfValidatingPassport(new UserBadge($frontendUser['id']));

        self::assertEquals($expectedPassport, $passport);
    }
}
