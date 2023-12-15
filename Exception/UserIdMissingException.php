<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Exception;

class UserIdMissingException extends \Exception
{
    /**
     * @param array<mixed> $userData
     */
    public function __construct(
        public readonly array $userData,
    ) {
        parent::__construct('"id" missing');
    }
}
