<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Integration\Security;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
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
    private const USER_PASSWORD = 'password';

    protected ContainerInterface $container;
    private Authenticator $authenticator;
    private Token $apiToken;
    private string $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();

        $authenticator = $this->container->get(Authenticator::class);
        \assert($authenticator instanceof Authenticator);
        $this->authenticator = $authenticator;

        $usersClient = $this->container->get(Client::class);
        \assert($usersClient instanceof Client);

        $frontendToken = $usersClient->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof RefreshableToken);

        $frontendUser = $usersClient->verifyFrontendToken($frontendToken->token);
        \assert($frontendUser instanceof User);
        $this->userId = $frontendUser->id;

        $apiKeys = $usersClient->listUserApiKeys($frontendToken->token);
        $apiKey = $apiKeys->getDefault();
        \assert($apiKey instanceof ApiKey);

        $apiToken = $usersClient->createApiToken($apiKey->key);
        \assert($apiToken instanceof Token);
        $this->apiToken = $apiToken;
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
        $passport = $this->authenticator->authenticate(new Request(server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->apiToken->token
        ]));

        $expectedPassport = new SelfValidatingPassport(new UserBadge($this->userId));

        self::assertEquals($expectedPassport, $passport);
    }
}
