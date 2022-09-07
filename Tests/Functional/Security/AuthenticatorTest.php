<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use SmartAssert\UsersClient\Client as UsersServiceClient;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersSecurityBundle\Security\Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use webignition\ObjectReflector\ObjectReflector;

class AuthenticatorTest extends AbstractBaseFunctionalTest
{
    use MockeryPHPUnitIntegration;

    private const USER_TOKEN =
        'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.' .
        'eyJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20iLCJzdWIiOiIwMUZQWkdIQUc2NUUwTjlBUldHNlkxUkgzNCIsImF1ZCI6WyJhcGkiXX0.' .
        'hMGV5MJexFIDIuh5gsqkhJ7C3SDQGnOW7sZVS5b6X08';

    private const USER_ID = '01FPZGHAG65E0N9ARWG6Y1RH34';

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
     * @dataProvider authenticateFailureDataProvider
     *
     * @param non-empty-string $userTokenValue
     */
    public function testAuthenticateFailure(string $userTokenValue, Request $request): void
    {
        $usersServiceClient = $this->createUsersServiceClient(new Token($userTokenValue), null);
        $this->setUsersServiceClientOnAuthenticator($usersServiceClient);

        self::expectExceptionObject(
            new CustomUserMessageAuthenticationException('Invalid user token')
        );

        $this->authenticator->authenticate($request);
    }

    /**
     * @return array<mixed>
     */
    public function authenticateFailureDataProvider(): array
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
        $token = new Token(self::USER_TOKEN);
        $userIdentifier = md5((string) rand()) . '@example.com';
        $user = new User(self::USER_ID, $userIdentifier);

        $usersServiceClient = $this->createUsersServiceClient($token, $user);
        $this->setUsersServiceClientOnAuthenticator($usersServiceClient);

        $passport = $this->authenticator->authenticate(new Request(server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::USER_TOKEN
        ]));
        $expectedPassport = new SelfValidatingPassport(new UserBadge(self::USER_ID));

        self::assertEquals($expectedPassport, $passport);
    }

    private function createUsersServiceClient(Token $token, ?User $returnValue): UsersServiceClient
    {
        $client = \Mockery::mock(UsersServiceClient::class);
        $client
            ->shouldReceive('verifyApiToken')
            ->withArgs(function (Token $passedToken) use ($token) {
                self::assertSame($token->token, $passedToken->token);

                return true;
            })
            ->andReturn($returnValue)
        ;

        return $client;
    }

    private function setUsersServiceClientOnAuthenticator(UsersServiceClient $client): void
    {
        ObjectReflector::setProperty(
            $this->authenticator,
            Authenticator::class,
            'usersServiceClient',
            $client
        );
    }
}
