<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Integration\Security;

use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersSecurityBundle\Security\Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticatorTest extends AbstractBaseIntegrationTest
{
    private Authenticator $authenticator;

    protected function setUp(): void
    {
        parent::setUp();

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
        $usersClient = $this->container->get(Client::class);
        \assert($usersClient instanceof Client);

        $frontendToken = $usersClient->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof RefreshableToken);

        $frontendUser = $usersClient->verifyFrontendToken($frontendToken);
        \assert($frontendUser instanceof User);

        $apiKeys = $usersClient->listUserApiKeys($frontendToken);
        $apiKey = $apiKeys->getDefault();
        \assert($apiKey instanceof ApiKey);

        $apiToken = $usersClient->createApiToken($apiKey->key);
        \assert($apiToken instanceof Token);

        $passport = $this->authenticator->authenticate(new Request(server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $apiToken->token
        ]));

        $expectedPassport = new SelfValidatingPassport(new UserBadge($frontendUser->id));

        self::assertEquals($expectedPassport, $passport);
    }
}
