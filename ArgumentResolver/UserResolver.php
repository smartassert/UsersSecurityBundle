<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\ArgumentResolver;

use SmartAssert\UsersSecurityBundle\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserResolver implements ValueResolverInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @return array<User>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (User::class !== $argument->getType()) {
            return [];
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return [$user];
    }
}
