<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<UserInterface>
 */
readonly class UserProvider implements UserProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private SymfonyRequestTokenExtractor $tokenExtractor,
    ) {}

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
        $identifier = trim($identifier);
        if ('' === $identifier) {
            return new EmptyUser();
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            return new InvalidUser($identifier);
        }

        $securityToken = (string) $this->tokenExtractor->extract($currentRequest);
        if ('' === $securityToken) {
            return new InvalidUser($identifier);
        }

        return new User($identifier, $securityToken);
    }
}
