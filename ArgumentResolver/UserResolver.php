<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\ArgumentResolver;

use SmartAssert\UsersSecurityBundle\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class UserResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return User::class === $argument->getType();
    }

    /**
     * @return \Traversable<User>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        yield $user;
    }
}