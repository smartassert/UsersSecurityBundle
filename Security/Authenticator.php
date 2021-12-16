<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

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
        private TokenExtractor $tokenExtractor,
        private UsersClient $usersServiceClient
    ) {
    }

    public function supports(Request $request): bool
    {
        return false !== $this->tokenExtractor->extract($request);
    }

    public function authenticate(Request $request): Passport
    {
        $token = (string) $this->tokenExtractor->extract($request);
        $userId = $this->usersServiceClient->verifyApiToken($token);
        if (null === $userId) {
            throw new CustomUserMessageAuthenticationException('Invalid user token');
        }

        return new SelfValidatingPassport(new UserBadge($userId));
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
