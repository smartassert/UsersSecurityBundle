<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

class AuthorizationProperties
{
    public const DEFAULT_HEADER_NAME = 'Authorization';
    public const DEFAULT_VALUE_PREFIX = 'Bearer ';

    public function __construct(
        private string $headerName = self::DEFAULT_HEADER_NAME,
        private string $valuePrefix = self::DEFAULT_VALUE_PREFIX,
    ) {
    }

    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    public function getValuePrefix(): string
    {
        return $this->valuePrefix;
    }
}
