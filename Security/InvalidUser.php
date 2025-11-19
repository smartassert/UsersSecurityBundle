<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

readonly class InvalidUser extends AbstractUser implements UserInterface {}
