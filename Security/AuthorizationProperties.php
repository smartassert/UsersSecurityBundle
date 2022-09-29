<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

class AuthorizationProperties
{
    public const DEFAULT_HEADER_NAME = 'Authorization';
    public const DEFAULT_VALUE_PREFIX = 'Bearer ';

    public function __construct(
        public readonly string $headerName = self::DEFAULT_HEADER_NAME,
        public readonly string $valuePrefix = self::DEFAULT_VALUE_PREFIX,
    ) {
    }
}
