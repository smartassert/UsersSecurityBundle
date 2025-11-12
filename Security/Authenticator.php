<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use SmartAssert\UsersSecurityBundle\Exception\HttpClientException;
use SmartAssert\UsersSecurityBundle\Exception\HttpResponseException;
use SmartAssert\UsersSecurityBundle\Exception\UserIdMissingException;
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
        private readonly ApiTokenVerifier $apiTokenVerifier,
    ) {
    }

    public function supports(Request $request): bool
    {
        return null !== $this->tokenExtractor->extract($request);
    }

    /**
     * @throws HttpClientException
     * @throws HttpResponseException
     * @throws UserIdMissingException
     */
    public function authenticate(Request $request): Passport
    {
        $tokenValue = trim((string) $this->tokenExtractor->extract($request));

        if ('' === $tokenValue) {
            throw new CustomUserMessageAuthenticationException('Invalid user token');
        }

        $id = $this->apiTokenVerifier->verify($tokenValue);
        if (null === $id) {
            throw new CustomUserMessageAuthenticationException('Invalid user token');
        }

        return new SelfValidatingPassport(new UserBadge($id));
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
