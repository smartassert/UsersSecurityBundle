<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User extends AbstractUser implements UserInterface
{
    /**
     * @param non-empty-string $securityToken
     */
    public function __construct(
        string $identifier,
        private readonly string $securityToken,
    ) {
        parent::__construct($identifier);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [
            'ROLE_USER',
        ];
    }

    /**
     * @return non-empty-string
     */
    public function getSecurityToken(): string
    {
        return $this->securityToken;
    }
}
