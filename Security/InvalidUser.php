<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class InvalidUser extends AbstractUser implements UserInterface
{
}
