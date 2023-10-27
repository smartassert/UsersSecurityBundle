<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client as UsersClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class Authenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly SymfonyRequestTokenExtractor $tokenExtractor,
        private readonly UsersClient $usersServiceClient,
    ) {
    }

    public function supports(Request $request): bool
    {
        return false !== $this->tokenExtractor->extract($request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function authenticate(Request $request): Passport
    {
        $tokenValue = trim((string) $this->tokenExtractor->extract($request));

        if ('' === $tokenValue) {
            throw new CustomUserMessageAuthenticationException('Invalid user token');
        }

        $user = $this->usersServiceClient->verifyApiToken($tokenValue);
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException('Invalid user token');
        }

        return new SelfValidatingPassport(new UserBadge($user->id));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('', Response::HTTP_UNAUTHORIZED);
    }
}
