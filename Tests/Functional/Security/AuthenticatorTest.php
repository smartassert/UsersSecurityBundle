<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Tests\Functional\Security;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use SmartAssert\UsersClient\Client as UsersServiceClient;
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
     * @dataProvider authenticateFailureDataProvider
     */
    public function testAuthenticateFailure(?string $userToken): void
    {
        $requestHeaders = [];
        if (is_string($userToken)) {
            $requestHeaders['HTTP_AUTHORIZATION'] = 'Bearer ' . $userToken;
        }

        $usersServiceClient = $this->createUsersServiceClient((string) $userToken, null);
        $this->setUsersServiceClientOnAuthenticator($usersServiceClient);

        self::expectExceptionObject(
            new CustomUserMessageAuthenticationException('Invalid user token')
        );

        $this->authenticator->authenticate(new Request(server: $requestHeaders));
    }

    /**
     * @return array<mixed>
     */
    public function authenticateFailureDataProvider(): array
    {
        return [
            'no user token' => [
                'userToken' => null,
                'request' => new Request(),
            ],
            'invalid user token' => [
                'userToken' => 'invalid-token',
            ],
        ];
    }

    public function testAuthenticateSuccess(): void
    {
        $usersServiceClient = $this->createUsersServiceClient(self::USER_TOKEN, self::USER_ID);
        $this->setUsersServiceClientOnAuthenticator($usersServiceClient);

        $passport = $this->authenticator->authenticate(new Request(server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::USER_TOKEN
        ]));
        $expectedPassport = new SelfValidatingPassport(new UserBadge(self::USER_ID));

        self::assertEquals($expectedPassport, $passport);
    }

    private function createUsersServiceClient(string $token, ?string $returnValue): UsersServiceClient
    {
        $client = \Mockery::mock(UsersServiceClient::class);
        $client
            ->shouldReceive('verifyApiToken')
            ->with($token)
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
