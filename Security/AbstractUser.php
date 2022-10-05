<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractUser implements UserInterface
{
    public function __construct(
        private readonly string $identifier,
    ) {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
