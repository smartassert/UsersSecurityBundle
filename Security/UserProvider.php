<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SymfonyRequestTokenExtractor $tokenExtractor,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            return new InvalidUser();
        }

        $securityToken = (string) $this->tokenExtractor->extract($currentRequest);
        if ('' === $securityToken) {
            return new InvalidUser();
        }

        return new User($identifier, $securityToken);
    }
}
