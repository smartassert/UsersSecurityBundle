<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

abstract readonly class AbstractUser implements UserInterface
{
    /**
     * @param non-empty-string $identifier
     */
    public function __construct(
        private string $identifier,
    ) {}

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void {}

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
